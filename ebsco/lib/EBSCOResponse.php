<?php


/**
 * EBSCO Response class
 *
 * PHP version 5
 *
 *
 * Copyright [2014] [EBSCO Information Services]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
    * Parse the SimpleXml object when a Search API call was executed
    *
    * @param none
    *
    * @return array An associative array of data
    * @access private
    */
    private function buildSearch()
    {
      $hits = (integer) $this->response->SearchResult->Statistics->TotalHits;
      $searchTime = (integer) $this->response->SearchResult->Statistics->TotalSearchTime / 1000;
      $queryString = (string)$this->response->SearchRequestGet->QueryString;
      $records = array();
      $facets = array();
      $queries = array();
      $appliedFacets = array();
      $appliedLimiters = array();
      $appliedExpanders = array();
      $relatedRecords = array();
      if($this->response->SearchRequestGet->SearchCriteriaWithActions->QueriesWithAction){
        $queriesWithAction = $this->response->SearchRequestGet->SearchCriteriaWithActions->QueriesWithAction->QueryWithAction;
        foreach($queriesWithAction as $queryWithAction){
          $queries[]=array(
            'query' => (string)$queryWithAction->Query->Term,
            'removeAction'=> (string) $queryWithAction->RemoveAction
          );
        }
      }
      if($this->response->SearchRequestGet->SearchCriteriaWithActions->FacetFiltersWithAction){
        $facetFiltersWithAction = $this->response->SearchRequestGet->SearchCriteriaWithActions->FacetFiltersWithAction->FacetFilterWithAction;
        foreach($facetFiltersWithAction as $facetFilterWithAction){
          $facetValue = array();
          foreach($facetFilterWithAction->FacetValuesWithAction->FacetValueWithAction as $facetValueWithAction){
            $facetValue[] = array(
              'Id' => (string)$facetValueWithAction->FacetValue->Id,
              'value'=>(string)$facetValueWithAction->FacetValue->Value,
              'removeAction'=>(string)$facetValueWithAction->RemoveAction
            );
          }
          $appliedFacets[] = array(
            'filterId' => (string)$facetFilterWithAction->FilterId,
            'facetValue'=> $facetValue,
            'removeAction'=> (string)$facetFilterWithAction->RemoveAction
          );
        }
      }
      if($this->response->SearchRequestGet->SearchCriteriaWithActions->LimitersWithAction){
        $limitersWithAction = $this->response->SearchRequestGet->SearchCriteriaWithActions->LimitersWithAction->LimiterWithAction;
        foreach($limitersWithAction as $limiterWithAction){
          $limiterValue = array(
          'value' => (string) $limiterWithAction->LimiterValuesWithAction->LimiterValueWithAction->Value,
          'removeAction'=> (string) $limiterWithAction->LimiterValuesWithAction->LimiterValueWithAction->RemoveAction
          );
          $appliedLimiters[] = array(
          'Id' => (string)$limiterWithAction->Id,
          'limiterValue'=>$limiterValue,
          'removeAction'=> (string) $limiterWithAction->RemoveAction
          );
        }
      }
      if($this->response->SearchRequestGet->SearchCriteriaWithActions->ExpandersWithAction){
        $expandersWithAction = $this->response->SearchRequestGet->SearchCriteriaWithActions->ExpandersWithAction->ExpanderWithAction;
        foreach($expandersWithAction as $expanderWithAction){
          $appliedExpanders[] = array(
          'Id' => (string)$expanderWithAction->Id,
          'removeAction'=>(string)$expanderWithAction->RemoveAction
          );
        }
      }
      if($this->response->SearchResult->RelatedContent){
        $relatedRecsWithAction = $this->response->SearchResult->RelatedContent->RelatedRecords;
        foreach($relatedRecsWithAction->RelatedRecord as $relRecs){
          if ($relRecs->Type == "rs") {
            foreach($relRecs->Records->Record as $rec) {
              $items=array();
              foreach ($rec->Items->Item as $item) {
                $items[] = array(
                'Name' => (string)$item->Name,
                'Label' => (string)$item->Label,
                'Group' => (string)$item->Group,
                'Data' => (string)$item->Data,
                );
              }
              $records[] = array(
              'Id' => (string)$rec->ResultId,
              'DbId' => (string)$rec->Header->DbId,
              'DbLabel'=>(string)$rec->Header->DbLabel,
              'An'=>(string)$rec->Header->An,
              'PLink'=>(string)$rec->PLink,
              'ImageInfo'=>(string)$rec->ImageInfo->CoverArt->Target,
              'FullText'=>(string)$rec->FullText,
              'Items'=>$items
              );
            }
            $relatedRecords[] = array(
            'Label' => (string)$relRecs->Label,
            'records' => $records
            );
          }
        }
      }
      if ($hits > 0) {
        $records = $this->buildRecords();
        $facets = $this->buildFacets();
      }
      $results = array(
      'recordCount' => $hits,
      'searchTime'  => $searchTime,
      'queryString' => $queryString,
      'numFound'    => $hits,
      'queries' => $queries,
      'appliedFacets'=>$appliedFacets,
      'appliedLimiters'=>$appliedLimiters,
      'appliedExpanders'=>$appliedExpanders,
      'relatedRecords'=>$relatedRecords,
      'documents' => $records,
      'facets' => $facets
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
            $result['AccessLevel'] = $record->Header->AccessLevel ? (string) $record->Header->AccessLevel : '';
            $result['PubType'] = $record->Header->PubType ? (string) $record->Header->PubType : '';
            $result['PubTypeId']=$record->Header->PubTypeId? (string) $record->Header->PubTypeId:'';
            $result['ResultId'] = $record->ResultId ? (integer) $record->ResultId : '';
            $result['DbId'] = $record->Header->DbId ? (string) $record->Header->DbId : '';
            $result['DbLabel'] = $record->Header->DbLabel ? (string) $record->Header->DbLabel : '';
            $result['An'] = $record->Header->An ? (string) $record->Header->An : '';
            $result['PLink'] = $record->PLink ? (string) $record->PLink : '';
            $result['PDF'] = $record->FullText->Links ? (string) $record->FullText->Links->Link->Type : '';
            $result['HTML'] = $record->FullText->Text->Availability? (string) $record->FullText->Text->Availability : '';
            $result['id'] = $result['An'] . '|' . $result['DbId'];
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

             if ($record->FullText->CustomLinks) {
                $result['FullTextCustomLinks'] = array();
                foreach ($record->FullText->CustomLinks->CustomLink as $customLink) {
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
             
             if ($record->FullTextHoldings) {
                $result['FullTextHoldings'] = array();
                foreach ($record->FullTextHoldings->FullTextHolding as $FullTextHolding) {
                    $name = $FullTextHolding->Name ? (string) $FullTextHolding->Name : '';
                    $url = $FullTextHolding->URL ? (string) $FullTextHolding->URL : '';
                    $result['FullTextHoldings'][] = array(
                        'Name'          => $name,
                        'URL'           => $url
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

            if($record->RecordInfo){
               $result['RecordInfo'] = array();
               $result['RecordInfo']['BibEntity']=array(
                   'Identifiers'=>array(),
                   'Languages'=>array(),
                   'PhysicalDescription'=>array(),
                   'Subjects'=>array(),
                   'Titles'=>array()
               );

               if($record->RecordInfo->BibRecord->BibEntity->Identifiers){
               foreach($record->RecordInfo->BibRecord->BibEntity->Identifiers->Identifier as $identifier){
                   $type = $identifier->Type? (string) $identifier->Type:'';
                   $value = $identifier->Value? (string) $identifier->Value:'';
                   $result['RecordInfo']['BibEntity']['Identifiers'][]= array(
                   'Type'=>$type,
                   'Value'=>$value
                   );
               }
               }

               if($record->RecordInfo->BibRecord->BibEntity->Languages){
               foreach($record->RecordInfo->BibRecord->BibEntity->Languages->Language as $language){
                   $code = $language->Code? (string)$language->Code:'';
                   $text = $language->Text? (string)$language->Text:'';
                   $result['RecordInfo']['BibEntity']['Languages'][]= array(
                   'Code'=>$code,
                   'Text'=>$text
                   );
               }
               }

               if($record->RecordInfo->BibRecord->BibEntity->PhysicalDescription){
               $pageCount = $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->PageCount? (string) $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->PageCount:'';
               $startPage = $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->StartPage? (string) $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->StartPage:'';
               $result['RecordInfo']['BibEntity']['PhysicalDescription']['Pagination'] = $pageCount;
               $result['RecordInfo']['BibEntity']['PhysicalDescription']['StartPage'] = $startPage;
               }

               if($record->RecordInfo->BibRecord->BibEntity->Subjects){
               foreach($record->RecordInfo->BibRecord->BibEntity->Subjects->Subject as $subject){
                   $subjectFull = $subject->SubjectFull? (string)$subject->SubjectFull:'';
                   $type = $subject->Type? (string)$subject->Type:'';
                   $result['RecordInfo']['BibEntity']['Subjects'][]=array(
                       'SubjectFull'=>$subjectFull,
                       'Type'=>$type
                   );
               }
               }

               if($record->RecordInfo->BibRecord->BibEntity->Titles){
               foreach($record->RecordInfo->BibRecord->BibEntity->Titles->Title as $title){
                   $titleFull = $title->TitleFull? (string)$title->TitleFull:'';
                   $type = $title->Type? (string)$title->Type:'';
                   $result['RecordInfo']['BibEntity']['Titles'][]=array(
                       'TitleFull'=>$titleFull,
                       'Type'=>$type
                   );
               }
               }

               $result['RecordInfo']['BibRelationships']=array(
                   'HasContributorRelationships'=>array(),
                   'IsPartOfRelationships'=>array()
               );

               if($record->RecordInfo->BibRecord->BibRelationships->HasContributorRelationships){
               foreach($record->RecordInfo->BibRecord->BibRelationships->HasContributorRelationships->HasContributor as $contributor){
                   $nameFull = $contributor->PersonEntity->Name->NameFull? (string)$contributor->PersonEntity->Name->NameFull:'';
                   $result['RecordInfo']['BibRelationships']['HasContributorRelationships'][]=array(
                       'NameFull'=>$nameFull
                   );
               }
               }

               if($record->RecordInfo->BibRecord->BibRelationships){
               foreach($record->RecordInfo->BibRecord->BibRelationships->IsPartOfRelationships->IsPartOf as $relationship){
                   if($relationship->BibEntity->Dates){
                       foreach($relationship->BibEntity->Dates->Date as $date){
                   $d = $date->D? (string)$date->D:'';
                   $m = $date->M? (string)$date->M:'';
                   $type = $date->Type? (string)$date->Type:'';
                   $y = $date->Y? (string)$date->Y:'';
                   $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['date'][] = array(
                     'D'=> $d,
                     'M'=>$m,
                     'Type'=>$type,
                     'Y'=>$y
                   );
                   }
                   }

                   if($relationship->BibEntity->Identifiers){
                   foreach($relationship->BibEntity->Identifiers->Identifier as $identifier){
                       $type = $identifier->Type? (string) $identifier->Type :'';
                       $value = $identifier->Value? (string) $identifier->Value:'';
                       $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Identifiers'][]=array(
                           'Type'=>$type,
                           'Value'=>$value
                       );
                   }
                   }

                   if($relationship->BibEntity->Titles){
                       foreach($relationship->BibEntity->Titles->Title as $title){
                          $titleFull = $title->TitleFull? (string)$title->TitleFull:'';
                          $type = $title->Type? (string)$title->Type:'';
                           $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Titles'][]=array(
                             'TitleFull' => $titleFull,
                             'Type'=>$type
                           );
                       }
                   }

                   if($relationship->BibEntity->Numbering){
                       foreach($relationship->BibEntity->Numbering->Number as $number){
                        $type = (string)$number->Type;
                        $value= (string)$number->Value;
                   $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['numbering'][] = array(
                     'Type'=> $type,
                     'Value'=>$value
                   );
                   }
                   }
               }
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
      if($this->response->SearchResult->AvailableFacets){
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
    }


    /**
    * Parse the SimpleXml object when an Info API call was executed
    *
    * @param none
    *
    * @return array An associative array of data
    * @access private
    */
    private function buildInfo()
    {
      // Sort options
      $sort = array();
      foreach ($this->response->AvailableSearchCriteria->AvailableSorts->AvailableSort as $element) {
        $sort[] = array(
          'Id' => (string) $element->Id,
          'Label' => (string) $element->Label,
          'Action' => (string) $element->AddAction
        );
      }
      // Search fields
      $search = array();
      foreach ($this->response->AvailableSearchCriteria->AvailableSearchFields->AvailableSearchField as $element) {
        $search[] = array(
          'Label' => (string) $element->Label,
          'Code' => (string) $element->FieldCode
        );
      }
      // Expanders
      $expanders = array();
      foreach ($this->response->AvailableSearchCriteria->AvailableExpanders->AvailableExpander as $element) {
        $expanders[] = array(
          'Id' => (string) $element->Id,
          'Label' => (string) $element->Label,
          'Action' => (string) $element->AddAction,
          'selected' => false // Added because of the checkboxes
        );
      }
      // Limiters
      $limiters = array();
      foreach ($this->response->AvailableSearchCriteria->AvailableLimiters->AvailableLimiter as $element) {
        $values = array();
        if ($element->LimiterValues) {
          $items = $element->LimiterValues->LimiterValue;
          foreach($items as $item) {
            $values[] = array(
              'Value' => (string) $item->Value,
              'Action' => (string) $item->AddAction,
              'selected' => false // Added because of the checkboxes
            );
          }
        }
        $limiters[] = array(
        'Id' => (string) $element->Id,
        'Label' => (string) $element->Label,
        'Action' => (string) $element->AddAction,
        'Type' => (string) $element->Type,
        'values' => $values,
        'selected' => false // Added because of the checkboxes
        );
      }
      // related content
      $relatedcontent = array();
      foreach ($this->response->AvailableSearchCriteria->AvailableRelatedContent->AvailableRelatedContent as $element) {
        $values = array();
        $relatedcontent[] = array(
        'Type' => (string) $element->Type,
        'Label' => (string) $element->Label,
        'Action' => (string) $element->AddAction,
        'DefaultOn' => (string) $element->DefaultOn
        );
      }
      $result = array(
      'sort' => $sort,
      'search' => $search,
      'expanders' => $expanders,
      'limiters' => $limiters,
      'relatedcontent' => $relatedcontent
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
        $allowed_searchlink_groups = array('au','su');

        // Map xml tags to the HTML tags
        // This is just a small list, the total number of xml tags is far more greater

        $xml_to_html_tags = array(
          '<jsection' => '<section',
          '</jsection' => '</section',
          '<highlight' => '<span class="highlight"',
          '<highligh' => '<span class="highlight"', // Temporary bug fix
          '</highlight>' => '</span>', // Temporary bug fix
          '</highligh' => '</span>',
          '<text' => '<div',
          '</text' => '</div',
          '<title' => '<h2',
          '</title' => '</h2',
          '<anid' => '<p',
          '</anid' => '</p',
          '<aug' => '<strong',
          '</aug' => '</strong',
          '<hd' => '<h3',
          '</hd' => '</h3',
          '<linebr' => '<br',
          '</linebr' => '',
          '<olist' => '<ol',
          '</olist' => '</ol',
          '<reflink' => '<a',
          '</reflink' => '</a',
          '<blist' => '<p class="blist"',
          '</blist' => '</p',
          '<bibl' => '<a',
          '</bibl' => '</a',
          '<bibtext' => '<span',
          '</bibtext' => '</span',
          '<ref' => '<div class="ref"',
          '</ref' => '</div',
          '<ulink' => '<a',
          '</ulink' => '</a',
          '<superscript' => '<sup',
          '</superscript'=> '</sup',
          '<relatesTo' => '<sup',
          '</relatesTo' => '</sup',
          // A very basic security implementation, using a blackist instead of a whitelist as needed.
          // But the total number of xml tags is so large that we won't build a whitelist right now
          '<script' => '',
          '</script' => '',
          '<i>' => '',
          '</i>' => ''
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
