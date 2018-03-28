<?php 

// This file is to verify that the extractor will find actions and filters inside functions and classes, indenifying
// those filters and classes.


class something
{
    public $defaults;

    public function __construct()
    {
        /**
         * This is a docblock before the code
         */
        //	apply_filters('filter_w_docblock_in_fn_in_class', $defaults);

        /**
         * This is a codblock before code that comes before the filter
         */
        $something = 'somthing else';
        //	apply_filters('filter_w-o_docblock_in_fn_in_class', $defaults);
    }
}

function plainFunction($params)
{
    /**
     * This is a docblock before the code
     */
    //	apply_filters('filter_w_docblock_in_fn', $defaults);


    /**
     * This is a codblock before code that comes before the filter
     */
    $something = 'somthing else';
    //	apply_filters('filter_w-o_docblock_in_fn', $defaults);
}
