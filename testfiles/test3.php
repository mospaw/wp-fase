<?php 

$foo = 'bar';


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
}

/**
 *	Another doc block
 *	that should get detected
 */
