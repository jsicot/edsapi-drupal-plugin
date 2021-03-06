<?php


/**
 * EBSCO Response class
 *
 * PHP version 5
 *
 */

require_once 'sanitizer.class.php';


/**
 * EBSCOResponse class
 */
class EBSCOResponse
{

    /**
     * A SimpleXml object
     * @global object
     */
    private $response;


    /**
     * Constructor
     *
     * Sets up the EBSCO Response
     *
     * @param none
     *
     * @access public
     */
    public function __construct($response)
    {
        $this->response = $response;
    }


    /**
     * Returns the XML as an associative array of data
     *
     * @param none
     *
     * @return array      An associative array of data
     * @access public
     */
    public function result()
    {
        if (!empty($this->response->AuthToken)) {
            return $this->buildAuthenticationToken();
        } else if (!empty($this->response->SessionToken)) {
            return (string) $this->response->SessionToken;
        } else if (!empty($this->response->SearchResult)) {
            return $this->buildSearch();
        } else if(!empty($this->response->Record)) {
            return $this->buildRetrieve();
        } else if(!empty($this->response->AvailableSearchCriteria)) {
            return $this->buildInfo();
        } else { // Should not happen, it may be an exception
            return $this->response;
        }
    }


    /**
     * Parse the SimpleXml object when an AuthenticationToken API call was executed
     *
     * @param none
     *
     * @return array   An associative array of data
     * @access private
     */
     private function buildAuthenticationToken()
     {
        $token = (string) $this->response->AuthToken;
        $timeout = (integer) $this->response->AuthTimeout;

        $result = array(
            'authenticationToken'   => $token,
            'authenticationTimeout' => $timeout
        );

        return $result;
     }

    /**
     * Parse a SimpleXml object and 
     * return it as an associative array
     *
     * @param none
     *
     * @return array   An associative array of data
     * @access private
     */
    private function buildSearch()
    {
        $hits = (integer) $this->response->SearchResult->Statistics->TotalHits;
        $searchTime = (integer) $this->response->SearchResult->Statistics->TotalSearchTime / 1000;
        $records = array();
        $facets = array();
        if ($hits > 0) {
            $records = $this->buildRecords();
            $facets = $this->buildFacets();
        }

        $results = array(
            'recordCount' => $hits,
            'searchTime'  => $searchTime,
            'numFound'    => $hits,
            'start'       => 0,
            'documents'   => $records,
            'facets'      => $facets
        );

        return $results;
    }


    /**
     * Parse a SimpleXml object and 
     * return it as an associative array
     *
     * @param none
     *
     * @return array    An associative array of data
     * @access private
     */
    private function buildRecords()
    {
        $results = array();

        $records = $this->response->SearchResult->Data->Records->Record;
        foreach ($records as $record) {
            $result = array();

            $result['ResultId'] = $record->ResultId ? (integer) $record->ResultId : '';
            $result['DbId'] = $record->Header->DbId ? (string) $record->Header->DbId : '';
            $result['DbLabel'] = $record->Header->DbLabel ? (string) $record->Header->DbLabel : '';
            $result['An'] = $record->Header->An ? (string) $record->Header->An : '';
            $result['PubType'] = $record->Header->PubType ? (string) $record->Header->PubType : '';
            $result['AccessLevel'] = $record->Header->AccessLevel ? (string) $record->Header->AccessLevel : '';
            $result['id'] = $result['An'] . '|' . $result['DbId'];
            $result['PLink'] = $record->PLink ? (string) $record->PLink : '';
            if (!empty($record->ImageInfo->CoverArt)) {
                foreach ($record->ImageInfo->CoverArt as $image) {
                    $size = (string) $image->Size;
                    $target = (string) $image->Target;
                    $result['ImageInfo'][$size] = $target;
                }
            } else {
                $result['ImageInfo'] = '';
            }

            if ($record->FullText) {
                $availability = (integer) $record->FullText->Text->Availability == 1;
                $links = array();
				//RF 2012-12-18
				if (isset($record->FullText->Links))
				{
					foreach ($record->FullText->Links->Link as $link) {
						$type = (string) $link->Type;
						$url = (string) $link->Url;
						// If we have an empty url when type is pdflink then just return something so
						// that the UI check for empty string will pass.
						$url = empty($url) && $type == 'pdflink' ? 'http://content.ebscohost.com' : $url;
						$links[$type] = $url;
					}
				}
                $result['FullText'] = array(
                    'Availability' => $availability,
                    'Links'        => $links
                );
            }

            if ($record->CustomLinks) {
                $result['CustomLinks'] = array();
                foreach ($record->CustomLinks->CustomLink as $customLink) {
                    $category = $customLink->Category ? (string) $customLink->Category : '';
                    $icon = $customLink->Icon ? (string) $customLink->Icon : '';
                    $mouseOverText = $customLink->MouseOverText ? (string) $customLink->MouseOverText : '';
                    $name = $customLink->Name ? (string) $customLink->Name : '';
                    $text = $customLink->Text ? (string) $customLink->Text : '';
                    $url = $customLink->Url ? (string) $customLink->Url : '';
                    $result['CustomLinks'][] = array(
                        'Category'      => $category,
                        'Icon'          => $icon,
                        'MouseOverText' => $mouseOverText,
                        'Name'          => $name,
                        'Text'          => $text,
                        'Url'           => $url
                    );
                }
             }

            if($record->Items) {
                $result['Items'] = array();
                foreach ($record->Items->Item as $item) {
                    $name = $item->Name ? (string) $item->Name : '';
                    $label = $item->Label ? (string) $item->Label : '';
                    $group = $item->Group ? (string) $item->Group : '';
                    $data = $item->Data ? (string) $item->Data : '';
                    $result['Items'][$name] = array(
                        'Name'  => $name,
                        'Label' => $label,
                        'Group' => $group,
                        'Data'  => $this->toHTML($data, $group)
                    );
                }
            }

            $results[] = $result;
        }

        return $results;
    }


     /**
     * Parse a SimpleXml object and 
     * return it as an associative array
     *
     * @param none
     *
     * @return array    An associative array of data
     * @access private
     */
    private function buildFacets()
    {
        $results = array();

        $facets = $this->response->SearchResult->AvailableFacets->AvailableFacet;
        foreach ($facets as $facet) {
            $values = array();
            foreach ($facet->AvailableFacetValues->AvailableFacetValue as $value) {
               $this_value = (string) $value->Value;
               $this_value = str_replace(array('\(','\)'), array('(', ')'), $this_value);
               $this_action = (string) $value->AddAction;
               $this_action = str_replace(array('\(','\)'), array('(', ')'), $this_action);
               $values[] = array(
                   'Value'  => $this_value,
                   'Action' => $this_action,
                   'Count'  => (string) $value->Count
               );
            }
            $id = (string) $facet->Id;
            $label = (string) $facet->Label;
            if (!empty($label)) {
                $results[] = array(
                    'Id'        => $id,
                    'Label'     => $label,
                    'Values'    => $values,
                    'isApplied' => false
                );
            }
        }

        return $results;
    }


    /**
     * Parse a SimpleXml object and 
     * return it as an associative array
     *
     * @param none
     *
     * @return array      An associative array of data
     * @access private
     */
    private function buildInfo()
    {
        // Sort options
        $elements = $this->response->AvailableSearchCriteria->AvailableSorts->AvailableSort;
        $sort = array();
        foreach ($elements as $element) {
            $sort[] = array(
                'Id'     => (string) $element->Id,
                'Label'  => (string) $element->Label,
                'Action' => (string) $element->AddAction
            );
        }

        // Search fields
        $elements = $this->response->AvailableSearchCriteria->AvailableSearchFields->AvailableSearchField;
        $tags = array();
        foreach ($elements as $element) {
            $tags[] = array(
                'Label' => (string) $element->Label,
                'Code'  => (string) $element->FieldCode
            );
        }

        // Expanders
        $elements = $this->response->AvailableSearchCriteria->AvailableExpanders->AvailableExpander;
        $expanders = array();
        foreach ($elements as $element) {
            $expanders[] = array(
                'Id'       => (string) $element->Id,
                'Label'    => (string) $element->Label,
                'Action'   => (string) $element->AddAction,
                'selected' => false // Added because of the checkboxes
            );
        }

        // Limiters
        $elements = $this->response->AvailableSearchCriteria->AvailableLimiters->AvailableLimiter;
        $limiters = array();
		$values = array();
        foreach ($elements as $element) {
            if ($element->LimiterValues) {
                $items = $element->LimiterValues->LimiterValue;
                foreach($items as $item) {
                    $values[] = array(
                        'Value'    => (string) $item->Value,
                        'Action'   => (string) $item->AddAction,
                        'selected' => false // Added because of the checkboxes
                    );
                }
            }
            $limiters[] = array(
                'Id'       => (string) $element->Id,
                'Label'    => (string) $element->Label,
                'Action'   => (string) $element->AddAction,
                'Type'     => (string) $element->Type,
                'Values'   => $values,
                'selected' => false
            );
        }

        $result = array(
            'sort'      => $sort,
            'tags'      => $tags,
            'expanders' => $expanders,
            'limiters'  => $limiters
        );

        return $result;
    }


    /**
     * Parse a SimpleXml object and 
     * return it as an associative array
     *
     * @param none
     *
     * @return array      An associative array of data
     * @access private
     */
    private function buildRetrieve()
    {
        $record = $this->response->Record;
        if ($record) {
            $record = $record[0]; // there is only one record
        }

        $result = array();
        $result['DbId'] = $record->Header->DbId ? (string) $record->Header->DbId : '';
        $result['DbLabel'] = $record->Header->DbLabel ? (string) $record->Header->DbLabel : '';
        $result['An'] = $record->Header->An ? (string) $record->Header->An : '';
        $result['id'] = $result['An'] . '|' . $result['DbId'];
        $result['PubType'] = $record->Header->PubType ? (string) $record->Header->PubType : '';
        $result['AccessLevel'] = $record->Header->AccessLevel ? (string) $record->Header->AccessLevel : '';
        $result['PLink'] = $record->PLink ? (string) $record->PLink : '';
        if (!empty($record->ImageInfo->CoverArt)) {
            foreach ($record->ImageInfo->CoverArt as $image) {
                $size = (string) $image->Size;
                $target = (string) $image->Target;
                $result['ImageInfo'][$size] = $target;
            }
        } else {
            $result['ImageInfo'] = '';
        }
        if ($record->FullText) {
            $availability = (integer) ($record->FullText->Text->Availability) == 1;
            $links = array();
            foreach ($record->FullText->Links->Link as $link) {
                $type = (string) $link->Type;
                $url = (string) $link->Url;
                // If we have an empty url when type is pdflink then just return something so
                // that the UI check for empty string will pass.
                $url = empty($url) && $type == 'pdflink' ? 'http://content.ebscohost.com' : $url;
                $links[$type] = $url;
            }
            $value = $this->toHTML($record->FullText->Text->Value);
            $result['FullText'] = array(
                'Availability' => $availability,
                'Links'        => $links,
                'Value'        => $value
            );
        }

        if ($record->CustomLinks) {
            $result['CustomLinks'] = array();
            foreach ($record->CustomLinks->CustomLink as $customLink) {
                $category = $customLink->Category ? (string) $customLink->Category : '';
                $icon = $customLink->Icon ? (string) $customLink->Icon : '';
                $mouseOverText = $customLink->MouseOverText ? (string) $customLink->MouseOverText : '';
                $name = $customLink->Name ? (string) $customLink->Name : '';
                $text = $customLink->Text ? (string) $customLink->Text : '';
                $url = $customLink->Url ? (string) $customLink->Url : '';
                $result['CustomLinks'][] = array(
                    'Category'      => $category,
                    'Icon'          => $icon,
                    'MouseOverText' => $mouseOverText,
                    'Name'          => $name,
                    'Text'          => $text,
                    'Url'           => $url
                );
            }
        }

        if($record->Items) {
            $result['Items'] = array();
            foreach ($record->Items->Item as $item) {
                $name = $item->Name ? (string) $item->Name : '';
                $label = $item->Label ? (string) $item->Label : '';
                $group = $item->Group ? (string) $item->Group : '';
                $data = $item->Data ? (string) $item->Data : '';
                $result['Items'][$name] = array(
                    'Name'  => $name,
                    'Label' => $label,
                    'Group' => $group,
                    'Data'  => $this->toHTML($data, $group)
                );
            }
        }

        return $result;
    }


    /**
     * Parse a SimpleXml element and 
     * return it's inner XML as an HTML string
     *
     * @param SimpleXml $element  A SimpleXml DOM
     *
     * @return string            The HTML string
     * @access protected
     */
    private function toHTML($data, $group = null)
    {
        // Any group can be added here, but we only use Au (Author) 
        // Other groups, not present here, won't be transformed to HTML links
        $allowed_searchlink_groups = array('au');

        // Map xml tags to the HTML tags
        // This is just a small list, the total number of xml tags is far more greater
        $xml_to_html_tags = array(
            '<jsection'    => '<section',
            '</jsection'   => '</section',
            '<highlight'   => '<span class="highlight"',
            '<highligh'    => '<span class="highlight"', // Temporary bug fix
            '</highlight>' => '</span>', // Temporary bug fix
            '</highligh'   => '</span>',
            '<text'        => '<div',
            '</text'       => '</div',
            '<title'       => '<h2',
            '</title'      => '</h2',
            '<anid'        => '<p',
            '</anid'       => '</p',
            '<aug'         => '<p class="aug"',
            '</aug'        => '</p',
            '<hd'          => '<h3',
            '</hd'         => '</h3',
            '<linebr'      => '<br',
            '</linebr'     => '',
            '<olist'       => '<ol',
            '</olist'      => '</ol',
            '<reflink'     => '<a',
            '</reflink'    => '</a',
            '<blist'       => '<p class="blist"',
            '</blist'      => '</p',
            '<bibl'        => '<a',
            '</bibl'       => '</a',
            '<bibtext'     => '<span',
            '</bibtext'    => '</span',
            '<ref'         => '<div class="ref"',
            '</ref'        => '</div',
            '<ulink'       => '<a',
            '</ulink'      => '</a',
            '<superscript' => '<sup',
            '</superscript'=> '</sup',
            '<relatesTo'   => '<sup',
            '</relatesTo'  => '</sup'
        );

        // Map xml types to Search types used by the UI
        $xml_to_search_types = array(
            'au' => 'Author',
            'su' => 'Subject'
        );

        //  The XML data is XML escaped, let's unescape html entities (e.g. &lt; => <)
        $data = html_entity_decode($data);

        // Start parsing the xml data
        if (!empty($data)) {
            // Replace the XML tags with HTML tags
            $search = array_keys($xml_to_html_tags);
            $replace = array_values($xml_to_html_tags);
            $data = str_replace($search, $replace, $data);

            // Temporary : fix unclosed tags
            $data = preg_replace('/<\/highlight/', '</span>', $data);
            $data = preg_replace('/<\/span>>/', '</span>', $data);
            $data = preg_replace('/<\/searchLink/', '</searchLink>', $data);
            $data = preg_replace('/<\/searchLink>>/', '</searchLink>', $data);

            // Parse searchLinks
            if (!empty($group)) {
                $group = strtolower($group);
                if (in_array($group, $allowed_searchlink_groups)) {
                    $type = $xml_to_search_types[$group];
                    $path = url('ebsco/results', array('query' => array('type' => $type)));
                    $link_xml = '/<searchLink fieldCode="([^\"]*)" term="%22([^\"]*)%22">/';
                    $link_html = "<a href=\"{$path}&lookfor=$2\">";
                    $data = preg_replace($link_xml, $link_html, $data);
                    $data = str_replace('</searchLink>', '</a>', $data);
                }
            }

            // Replace the rest of searchLinks with simple spans
            $link_xml = '/<searchLink fieldCode="([^\"]*)" term="%22([^\"]*)%22">/';
            $link_html = '<span>';
            $data = preg_replace($link_xml, $link_html, $data);
            $data = str_replace('</searchLink>', '</span>', $data);

            // Parse bibliography (anchors and links)
            $data = preg_replace('/<a idref="([^\"]*)"/', '<a href="#$1"', $data);
            $data = preg_replace('/<a id="([^\"]*)" idref="([^\"]*)" type="([^\"]*)"/', '<a id="$1" href="#$2"', $data);
        }

        $sanitizer = new HTML_Sanitizer;
        $data = $sanitizer->sanitize($data);

        return $data;
    }


}

?>