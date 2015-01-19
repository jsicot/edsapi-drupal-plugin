<?php

/**
 * The EBSCO Document model class
 *
 * It provides all the methods and properties needed for :
 * - setting up and performing API calls
 * - displaying results in UI
 * - displaying statistics about the search, etc
 *
 * PHP version 5
 *
 */

require_once 'EBSCOAPI.php';
require_once 'EBSCORecord.php';


class EBSCODocument
{
    /**
     * The EBSCOAPI object that performs the API calls
     * @global object EBSCOAPI
     */
    private $eds = null;

    /**
     * The associative array of current request parameters
     * @global array
     */
    private $params = array();

    /**
     * The associative array of EBSCO results returned by a Search API call
     * #global array
     */
    private $results = array();

    /**
     * The associative array of data returned by a Retrieve API call
     * @global array
     */
    private $result = array();

    /**
     * The array of data returned by an Info API call
     * @global array
     */
    private $info = array();

    /**
     * The EBSCORecord model returned by a Retrieve API call
     * #global object EBSCORecord
     */
    private $record = null;

    /**
     * The array of EBSCORecord models returned by a Search API call
     * #global array of EBSCORecord objects
     */
    private $records = array();

    /**
     * The array of filters currently applied
     * @global array
     */
    private $filters = array();

    /**
     * Maximum number of results returned by Search API call
     * @global integer
     */
    private $limit = 10;

    /**
     * Maximum number of links displayed by the pagination
     * @global integer
     */
    private static $page_links = 10;

    /**
     * Limit options
     * global array
     */
    private static $limit_options = array(
        10 => 10,
        20 => 20,
        30 => 30,
        40 => 40,
        50 => 50
    );

    /**
     * Sort options
     * global array
     */
    private static $sort_options = array(
        'relevance' => 'Pertinence',
        'date_desc' => 'Du plus récent au plus ancien',
        'date_asc'  => 'Du plus ancien au plus récent'
    );

    /**
     * Amount options
     * global array
     */
    private static $amount_options = array(
        'detailed' => 'Detailed',
        'brief'    => 'Brief',
        'title'    => 'Title Only'
    );

    /**
     * Bool options
     * global array
     */
    private static $bool_options = array(
        'AND' => 'All terms',
        'OR'  => 'Any terms',
        'NOT' => 'No terms'
    );

    /**
     * Search mode options
     * global array
     */
    private static $mode_options = array(
        'all'   => 'All search terms',
        'bool'  => 'Boolean / Phrase',
        'any'   => 'Any search terms',
        'smart' => 'SmartText Searching'
    );

    /**
     * Basic search type options
     * global array
     */
    private static $basic_search_type_options = array(
        'AllFields' => 'All Text',
        'Title'     => 'Title',
        'Author'    => 'Author',
        'Subject'   => 'Subject terms',
        'Source'    => 'Source',
        'Abstract'  => 'Abstract'
    );

    /**
     * Advanced search type options
     * global array
     */
    private static $advanced_search_type_options = array(
        'AllFields' => 'All Text',
        'Title'     => 'Title',
        'Author'    => 'Author',
        'Subject'   => 'Subject terms'
    );


    /**
     * Constructor.
     *
     * @param array $data Raw data from the EBSCO search representing the record.
     */
    public function __construct($params = null)
    {
        $this->eds = new EBSCOAPI(array(
            'password'     => variable_get('ebsco_password'),
            'user'         => variable_get('ebsco_user'),
            'profile'      => variable_get('ebsco_profile'),
            'interface'    => variable_get('ebsco_interface'),
            'organization' => variable_get('ebsco_organization'),
            'guest'        => variable_get('ebsco_guest'),
            'log'          => variable_get('ebsco_log')
        ));

        $this->params = $params ? $params : $_REQUEST;
        $this->limit = variable_get('ebsco_default_limit') ? variable_get('ebsco_default_limit') : $this->limit;
    }


    /**
     * Perform the API Info call
     *
     * @return array
     */
    public function info()
    {
        $this->info = $this->eds->apiInfo();
        return $this->info;
    }


    /**
     * Perform the API Retrieve call
     *
     * @return array
     */
    public function retrieve()
    {
        list($an, $db) = isset($this->params['id']) ? explode('|', $this->params['id'], 2) : array(null, null);
        $this->result = $this->eds->apiRetrieve($an, $db);

        return $this->result;
    }


    /**
     * Perform the API Search call
     *
     * @return array
     */
    public function search()
    {
        $search = array();

        if (isset($this->params['lookfor']) && isset($this->params['type'])) {
            $search = array(
                'lookfor' => $this->params['lookfor'],
                'index'   => $this->params['type']
            );
        } else if (isset($this->params['group'])) {
            $search = $this->params;
        } else {
            return array();
        }

        $filter = isset($this->params['filter']) ? $this->params['filter'] : array();
        $page = isset($this->params['page']) ? $this->params['page'] + 1 : 1;
        $limit = $this->limit;
        $sort = isset($this->params['sort']) ? $this->params['sort'] : (variable_get('ebsco_default_sort') ? variable_get('ebsco_default_sort') : 'relevance');
        $amount = isset($this->params['amount']) ? $this->params['amount'] : (variable_get('ebsco_default_amount') ? variable_get('ebsco_default_amount') : 'detailed');
        $mode = isset($this->params['mode']) ? $this->params['mode'] : (variable_get('mode') ? variable_get('mode') : 'all');

        $this->results = $this->eds->apiSearch($search, $filter, $page, $limit, $sort, $amount, $mode);
        $this->results['Publication'] = $this->eds->apiPublication($search, $filter, $page, $limit, $sort, $amount);
        if (isset($this->results['start'])) {
            $this->results['start'] = $limit * ($page - 1);
        }

        return $this->results;
    }


    /**
     * Get the EBSCORecord model for the result
     *
     ** @return array
     */
    public function record()
    {
        if (empty($this->record) && !(empty($this->result))) {
            $this->record = new EBSCORecord($this->result);
        }

        return $this->record;
    }


    /**
     * Get the EBSCORecord models array from results array
     *
     ** @return array
     */
    public function records()
    {
        if (empty($this->records) && !(empty($this->results))) {
            foreach($this->results['documents'] as $result) {
                $this->records[] = new EBSCORecord($result);
            }
        }

        return $this->records;
    }



    /**
     * Get the EBSCORecord models array from results array
     *
     ** @return array
     */
    public function jsonObj()
    {
        if (empty($this->records) && !(empty($this->results))) {
            foreach($this->results['documents'] as $result) {
                $this->records[] = $result;
            }
        }

        return $this->records;
    }

    /**
     * Get the pagination HTML string
     *
     ** @return HTML string
     */
    public function pager()
    {
        $pager = null;
        if ($this->has_records()) {
            pager_default_initialize($this->record_count() / $this->limit, 1);
            $pager = theme('pager', array('tags' => null, 'quantity' => self::$page_links));
            $pager = preg_replace('/<li class="pager-last last">(.*)<\/li>/', '', $pager);
        }
        return $pager;
    }


    /********************************************************
     *
     * Getters (class methods)
     *
     ********************************************************/


    /**
     * Getter for sort options
     * @return array
     */
    public static function limit_options()
    {
        return self::$limit_options;
    }


    /**
     * Getter for sort options
     * @return array
     */
    public static function sort_options()
    {
        return self::$sort_options;
    }


    /**
     * Getter for amount options
     * @return array
     */
    public static function amount_options()
    {
        return self::$amount_options;
    }


    /**
     * Getter for boolean options
     * @return array
     */
    public static function bool_options()
    {
        return self::$bool_options;
    }


    /**
     * Getter for search mode options
     * @return array
     */
    public static function mode_options()
    {
        return self::$mode_options;
    }


    /**
     * Getter for Basic search type options
     * @return array
     */
    public static function basic_search_type_options()
    {
        return self::$basic_search_type_options;
    }


    /**
     * Getter for Advanced search type options
     * @return array
     */
    public static function advanced_search_type_options()
    {
        return self::$advanced_search_type_options;
    }


    /********************************************************
     *
     * Helper methods
     *
     ********************************************************/


    /**
     * Get the expanders.
     *
     * @return array
     */
    public function expanders()
    {
        $actions = array();
        $filters = $this->filters();
        foreach($filters as $filter) {
            $actions[] = $filter['action'];
        }

        $expanders = isset($this->info['expanders']) ? $this->info['expanders'] : array();
        foreach($expanders as $key => $expander) {
            if (in_array($expander['Action'], $actions)) {
                $expanders[$key]['selected'] = true;
            }
        }

        return $expanders;
    }


    /**
     * Get the facets.
     *
     * @return array
     */
    public function facets()
    {
        $actions = array();
        foreach($this->filters as $filter) {
            $actions[] = $filter['action'];
        }

        $facets = isset($this->results['facets']) ? $this->results['facets'] : array();
        foreach($facets as $key => $cluster) {
            foreach($cluster['Values'] as $k => $facet) {
                $is_applied = false;
                if (in_array($facet['Action'], $actions)) {
                    $is_applied = true;
                }
                $facets[$key]['Values'][$k]['applied'] = $is_applied;
            }
        }

        return $facets;
    }


    /**
     * Get the filters.
     *
     * @return array
     */
    public function filters()
    {
        if (!empty($_REQUEST['filter'])) {
            $labels = array();
            foreach($this->info['limiters'] as $limiter) {
                $labels[$limiter['Id']] = $limiter['Label'];
            }
            $this->filters = array();
            foreach($_REQUEST['filter'] as $filter) {
                if (!empty($filter)) {
                    $temp = str_replace(array('addfacetfilter(', 'addlimiter(', 'addexpander('), array('', '', ''), $filter);
                    if (substr($temp, -1, 1) == ')') {
                        $temp = substr($temp, 0, -1);
                    }
                    // Do not display addfacetfilter, addlimiter or addexpander strings
                    if (preg_match('/\:/', $filter)) {
                        list($field, $value) = explode(':', $temp, 2);
                        $displayField = isset($labels[$field]) ? $labels[$field] : $field;
                        $displayValue = $value == 'y' ? 'yes' : $value;
                    } else if (preg_match('/addexpander/', $filter)) {
                        $field = $temp;
                        $value = 'y';
                        $displayField = isset($labels[$field]) ? $labels[$field] : $field;
                        $displayValue = 'yes';
                    } else {
                        $field = $value = $displayField = $displayValue = $filter;
                    }

                    $this->filters[] = array(
                        'field'        => $field,
                        'value'        => $value,
                        'action'       => $filter,
                        'displayField' => $displayField,
                        'displayValue' => $displayValue,
                    );
                }
            }
        }
        return $this->filters;
    }


    /**
     * Get the limiters.
     *
     * @return array
     */
    public function limiters()
    {
        $actions = array(); $ids = array();
        $filters = $this->filters();
        foreach($filters as $filter) {
            $actions[] = $filter['action'];
            $ids[] = $filter['field'];
        }

        $limiters = isset($this->info['limiters']) ? $this->info['limiters'] : array();
        foreach($limiters as $key => $cluster) {
            // multi select limiter
            if (!empty($cluster['Values'])) {
                foreach($cluster['Values'] as $limiter) {
                    $action = $limiter['Action'];
                    if (in_array($action, $actions)) {
                        $limiters[$key]['selected'][] = $limiter['Action'];
                    }
                }
            // date limiter
            } else if ($cluster['Type'] == 'ymrange') {
                 $id = $cluster['Id'];
                 if (($k = array_search($id, $ids)) !== false) {
                        $limiters[$key]['selected'] = $filters[$k]['action'];
                    }
            // other limiters
            } else {
                $action = str_replace('value', 'y', $cluster['Action']);
                if (in_array($action, $actions)) {
                    $limiters[$key]['selected'] = true;
                }
            }
        }

        return $limiters;
    }


    /**
     * Get the total number of records.
     *
     * @return integer
     */
    public function record_count()
    {
        return !empty($this->results) ? $this->results['recordCount'] : 0;
    }


    /**
     * Get the number of end record.
     *
     * @return integer
     */
    public function record_end()
    {
        $count = !empty($this->results) ? count($this->results['documents']) : 0;
        $start = !empty($this->results) ? $this->results['start'] : 0;
        return $start + $count;
    }


    /**
     * Get the number of start record.
     *
     * @return integer
     */
    public function record_start()
    {
        return !empty($this->results) ? $this->results['start'] + 1 : 0;
    }


    /**
     * Get the search time
     *
     * @return decimal number
     */
    public function search_time()
    {
        return !empty($this->results) &&
            isset($this->results['searchTime']) ? $this->results['searchTime'] : 0;
    }


    /**
    * Get the search time
    *
    * @return decimal number
    */
    public function publication()
    {
      return !empty($this->results) &&
      isset($this->results['Publication']) ? $this->results['Publication'] : null;
    }


    /**
     * Get the search view : basic or advanced
     *
     * @return string
     */
    public function search_view()
    {
        if (isset($_REQUEST['group'])) {
            return 'advanced';
        } else {
            return 'basic';
        }
    }


    /**
     * Hidden params used by UpdateForm
     *
     * @return array
     */
    public function search_params()
    {
        $params = $this->link_search_params();
        // filter the params that have same values as sidebar checkboxes, otherwise they will produce duplicates
        $not_allowed_values = array(
            'addexpander(thesaurus)',
            'addexpander(fulltext)',
            'addlimiter(FT:y)',
            'addlimiter(RV:y)',
            'addlimiter(SO:y)'
        );

        $params = $this->array_filter_recursive($params, function($item) use($not_allowed_values) {
            return !($item && in_array($item, $not_allowed_values));
        });

        return array_filter($params);
    }


    /**
     * Hidden params used by UpdateForm
     *
     * @return array
     */
    public function link_search_params()
    {
        // filter the page parameter
        $not_allowed_keys = array('page', 'ui', 'has_js', 'op', 'submit', 'form_id', 'form_build_id');

		$query="";
		if(isset($_SERVER['QUERY_STRING']))
			{$query = urldecode($_SERVER['QUERY_STRING']);}
        parse_str($query, $params);

        $params = $this->array_unset_recursive($params, $not_allowed_keys);

        return $params;
    }


    /**
     * Check if there are records in results array
     *
     ** @return boolean
     */
    public function has_records()
    {
        return !empty($this->results) && !empty($this->results['documents']);
    }


    /**
     * Create the last search data
     *
     * @return void
     */
    public function search_create($query = null)
    {
        $last_search = array();
        if (!empty($this->results)) {
            $results_identifiers = array();
            foreach($this->results['documents'] as $result) {
                $results_identifiers[] = $result['id'];
            }
            $last_search['query'] = $query ? $query : $_SERVER['QUERY_STRING'];
            $last_search['records'] = serialize($results_identifiers);
            $last_search['count'] = $this->record_count();
        }

        return $last_search;
    }


    /**
     * Save last search data in session
     *
     * @return void
     */
    public function search_write($query = null)
    {
        $_SESSION['EBSCO']['last-search'] = $this->search_create($query);
    }


    /**
     * Load last search data from session
     *
     * @return array
     */
    public function search_read($id = null, $op = null)
    {
        $params = array();
        $lastSearch = $_SESSION['EBSCO']['last-search'];
        if ($lastSearch) {
            $lastSearch['records'] = unserialize($lastSearch['records']);
            if ($id) {
                parse_str($lastSearch['query'], $params);
                $params['page'] = (int) (isset($params['page']) ? $params['page'] : 0);
                $index = array_search($id, $lastSearch['records']);

                // if this is not the first scroll and if this is not a page refresh
                if (isset($lastSearch['current']) && $lastSearch['current'] != $id) {
                    // if we change page
                    if (($op == 'Next' && $index % $this->limit === 0) ||
                        ($op == 'Previous' && $index % $this->limit === 9)) {
                        $params['page'] = ($op == 'Next') ? $params['page'] + 1 : $params['page'] - 1;
                        $query = drupal_http_build_query($params);
                        $lastSearch['query'] = $_SESSION['EBSCO']['last-search']['query'] = $query;
                    }
                }
                $start = $params['page'];

                if (count($lastSearch['records']) > 10) {
                    $records = array_slice($lastSearch['records'], $index - $index % $this->limit, $this->limit);
                } else {
                    $records = $lastSearch['records'];
                }

                if (!isset($lastSearch['records'][$index + 1])) {
                    $params['page'] += 1;
                    $driver = new EBSCODocument($params);
                    $driver->search();
                    $query = drupal_http_build_query($params);
                    $newSearch = $driver->search_create($query);
                    $newSearch['records'] = unserialize($newSearch['records']);
                    $lastSearch['records'] = array_merge($lastSearch['records'], $newSearch['records']);
                    $_SESSION['EBSCO']['last-search']['records'] = serialize($lastSearch['records']);
                    if ($op == 'Next') {
                        $lastSearch['previous'] = isset($records[8]) ? $records[8] : '';
                    }
                    $lastSearch['next'] = isset($newSearch['records'][0]) ? $newSearch['records'][0] : '';
                } else {
                    $lastSearch['next'] =  $lastSearch['records'][$index + 1];
                }

                if (!isset($lastSearch['records'][$index - 1])) {
                    if ($params['page'] > 0) {
                        $params['page'] -= 1;
                        $driver = new EBSCODocument($params);
                        $driver->search();
                        $query = drupal_http_build_query($params);
                        $newSearch = $driver->search_create($query);
                        $newSearch['records'] = unserialize($newSearch['records']);
                        $lastSearch['records'] = array_merge($lastSearch['records'], $newSearch['records']);
                        $_SESSION['EBSCO']['last-search']['records'] = serialize($lastSearch['records']);
                        $lastSearch['previous'] = isset($newSearch['records'][9]) ? $newSearch['records'][9] : '';
                        if ($op == 'Previous') {
                            $lastSearch['next'] = isset($records[1]) ? $records[1] : '';
                        }
                    } else {
                        $lastSearch['previous'] = '';
                    }
                } else {
                    $lastSearch['previous'] = $lastSearch['records'][$index - 1];
                }

                $lastSearch['current_index'] = $start * $this->limit + $index % $this->limit + 1;
                $lastSearch['current'] = $id;
            }
        }

        $_SESSION['EBSCO']['last-search']['current'] = $id;
        return $lastSearch;
    }


    /**
     * A recursive array_filter
     *
     * @return array
     */
    private function array_filter_recursive($input, $callback = null)
    {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = $this->array_filter_recursive($value, $callback);
            }
        }
        return array_filter($input, $callback);
    }


    /**
     * Recursive filter an array using the given $keys
     *
     * @return array
     */
    private function array_unset_recursive($input, $keys) {
        foreach($keys as $key) {
            if (isset($input[$key])) {
                unset($input[$key]);
            }
        }

        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = is_array($value) ? $this->array_unset_recursive($value, $keys) : $value;
            }
        }

        return array_filter($input);
    }
}
