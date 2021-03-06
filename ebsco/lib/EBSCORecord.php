<?php
/**
 * The EBSCO record object
 *
 * PHP version 5
 *
 */

class EBSCORecord
{
    /**
     * The array of data
     * @global array
     */
    private $data = array();

    /**
     * The result id (the EBSCO counter) of the record
     * @global integer
     */
     public $result_id = null;

     /**
     * The id of the record
     * @global integer
     */
     public $record_id = null;

    /**
     * The summary of the record.
     * @global string
     */
    public $summary = null;

    /**
     * The authors of the record.
     * @global string
     */
    public $authors = null;

    /**
     * The subjects of the record.
     * @global string
     */
    public $subjects = null;

    /**
     * The custom links provided for the record.
     * @global array
     */
    public $custom_links = array();

    /**
     * The database label of the record.
     * @global string
     */
     public $db_label = null;

     /**
     * The full-text availability of the record.
     * @global boolean
     */
     public $full_text_availability = null;

     /**
     * The full text of the record.
     * @global string
     */
     public $full_text = null;

     /**
     * The PDF availability of the record.
     * @global boolean
     */
     public $pdf_availability = null;

    /**
     * The items of the record.
     * @global array
     */
    public $items = array();

    /**
     * The external link of the record.
     * @global string
     */
     public $p_link = null;

    /**
     * The external link to the PDF version of the record.
     * @global string
     */
     public $pdf_link = null;

     /**
     * The publication type link of the record.
     * @global string
     */
     public $publication_type = null;

     /**
     * The external thumbnails links of the record.
     * @global string
     */
     public $small_thumb_link = null;
     public $medium_thumb_link = null;

     /**
      * The title of the record.
      * @global string
      */
     public $title = null;

     /**
      * The source of the record.
      * @global string
      */
     public $source = null;

     /**
      * The access level of the record.
      * @global string
      */
     public $access_level = null;


    /**
     * Constructor.
     *
     * @param array $data Raw data from the EBSCO search representing the record.
     */
    public function __construct($data = array())
    {
        $this->data = $data;
        $this->record_id = $this->record_id();
        $this->result_id = $this->result_id();
        $this->title = $this->title();
        $this->summary = $this->summary();
        $this->authors = $this->authors();
        $this->subjects = $this->subjects();
        $this->custom_links = $this->custom_links();
        $this->db_label = $this->db_label();
        $this->full_text_availability = $this->full_text_availability();
        $this->full_text = $this->full_text();
        $this->items = $this->items();
        $this->p_link = $this->p_link();
        $this->publication_type = $this->publication_type();
        $this->pdf_availability = $this->pdf_availability();
        $this->pdf_link = $this->pdf_link();
        $this->small_thumb_link = $this->thumb_link();
        $this->medium_thumb_link = $this->thumb_link('medium');
        $this->source = $this->source();
        $this->access_level = $this->access_level();
    }


    /********************************************************
     *
     * Getters
     *
     ********************************************************/


    /**
     * Get the summary of the record.
     *
     * @return string
     */
    public function access_level()
    {
        return isset($this->data['AccessLevel']) ? 
            $this->data['AccessLevel'] : '';
    }


    /**
     * Get the summary of the record.
     *
     * @return string
     */
    public function summary()
    {
        return isset($this->data['Items']['Abstract']) ? 
            $this->data['Items']['Abstract']['Data'] : '';
    }


    /**
     * Get the authors of the record.
     *
     * @return string
     */
    public function authors()
    {
        return isset($this->data['Items']['Author']) ? 
            $this->data['Items']['Author']['Data'] : '';
    }


    /**
     * Get the custom links of the record.
     *
     * @return array
     */
    public function custom_links()
    {
        return isset($this->data['CustomLinks']) ?
            $this->data['CustomLinks'] : array();
    }


    /**
     * Get the database label of the record.
     *
     * @return string
     */
    public function db_label()
    {
        return isset($this->data['DbLabel']) ?
            $this->data['DbLabel'] : '';
    }


    /**
     * Get the full text availability of the record.
     *
     * @return boolean
     */
    public function full_text()
    {
        return isset($this->data['FullText']) && 
            isset($this->data['FullText']['Value']) ? $this->data['FullText']['Value'] : '';
    }


    /**
     * Get the full text availability of the record.
     *
     * @return boolean
     */
    public function full_text_availability()
    {
        return isset($this->data['FullText']) && 
            $this->data['FullText']['Availability'];
    }


    /**
     * Get the items of the record.
     *
     * @return array
     */
    public function items()
    {
        return isset($this->data['Items']) ? $this->data['Items'] : array();
    }


    /**
     * Get the external url of the record.
     *
     * @return string
     */
    public function p_link()
    {
        return isset($this->data['PLink']) ? $this->data['PLink'] : '';
    }


    /**
     * Get the publication type of the record.
     *
     * @return string
     */
    public function publication_type()
    {
        return isset($this->data['PubType']) ? $this->data['PubType'] : '';
    }


    /**
     * Get the PDF availability of the record.
     *
     * @return boolean
     */
    public function pdf_availability()
    {
        return isset($this->data['FullText']) && 
            isset($this->data['FullText']['Links']) && 
            isset($this->data['FullText']['Links']['pdflink']) && 
            $this->data['FullText']['Links']['pdflink'];
    }


    /**
     * Get the PDF url of the record.
     *
     * @return string
     */
    public function pdf_link()
    {
        return isset($this->data['FullText']) && 
            isset($this->data['FullText']['Links']) && 
            isset($this->data['FullText']['Links']['pdflink']) ? 
            $this->data['FullText']['Links']['pdflink'] :
            '';
    }


    /**
     * Get the result id of the record.
     *
     * @return integer
     */
    public function result_id()
    {
        return isset($this->data['ResultId']) ? 
            $this->data['ResultId'] : '';
    }


    /**
     * Get the subject data of the record.
     *
     * @return string
     */
    public function subjects()
    {
        return isset($this->data['Items']['Subject']) ? 
            $this->data['Items']['Subject']['Data'] : '';
    }


    /**
     * Return a URL to a thumbnail preview of the record, if available; false
     * otherwise.
     *
     * @param string $size Size of thumbnail (small, medium or large -- small is
     * default).
     *
     * @return string
     */
    public function thumb_link($size = 'small')
    {
        $imageInfo = isset($this->data['ImageInfo']) ? $this->data['ImageInfo'] : '';
        if ($imageInfo && isset($imageInfo['thumb'])) {
            switch ($size) {
                case 'large':
                case 'medium':
                    return $imageInfo['medium'];
                    break;

                case 'small':
                default:
                    return $imageInfo['thumb'];
                    break;
            }
        }
        return false;
    }


    /**
     * Get the title of the record.
     *
     * @return string
     */
    public function title()
    {
        return isset($this->data['Items']['Title']) ? 
            $this->data['Items']['Title']['Data'] : '';
    }


    /**
     * Get the source of the record.
     *
     * @return string
     */
    public function source()
    {
        return isset($this->data['Items']['TitleSource']) ? 
            $this->data['Items']['TitleSource']['Data'] : '';
    }


    /**
     * Return the identifier of this record within the EBSCO databases
     *
     * @return string Unique identifier.
     */
    public function record_id()
    {
        return isset($this->data['id']) ? 
            $this->data['id'] : '';
    }

}
