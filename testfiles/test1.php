<?php 

$foo = 'bar';

/**
  * this is an example doc block that should be detected
  */
$defaults = apply_filters('single_quote_name_no_spaces', $first = array('fake', array(1,2,3)), $second, $last);

/**
  * this is an example doc block that should NOT be detected
  */
$bar = 'baz';

/**
  * this is an example doc block that should NOT be detected
  */
function non_functional()
{
    $lots = of_code();
    //goes here

    /**
      * this is an example doc block that should be detected. It sits above an action inside a function.
      */
    $defaults = do_action('single_quote_name_no_spaces', $first = array('fake', array(1,2,3)), $second, $last);
}

/**
 *	Another doc block
 *	that should get detected
 */


$defaults2 = apply_filters(
    'single_quote_no_spaces2',
    $xtrafirst = array(
        'fake',
        array(
            1,
            2,
            3,
            array(
                4,
                5,
                6
            ),
        ),
    ),
    $xtrasecond = "VALUE",
    $xtralast = 'another value'
);


function apply_filters()
{
}



//$defaults = apply_filters('single_quote_name_no_spaces', $first = array('fake', array(1,2,3)), $second, $third);
