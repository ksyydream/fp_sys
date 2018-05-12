<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty truncate modifier plugin
 *
 * Type: modifier<br>
 * Name: truncate<br>
 * Purpose: Truncate a string to a certain length if necessary,
 * optionally splitting in the middle of a word, and
 * appending the $etc string or inserting $etc into the middle.
 * 
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php
 *       truncate (Smarty online manual)
 * @author Monte Ohrt <monte at ohrt dot com>
 * @param
 *        	string
 * @param
 *        	integer
 * @param
 *        	string
 * @param
 *        	boolean
 * @param
 *        	boolean
 * @return string
 */
function smarty_modifier_truncate_cn($string, $sublen = 80, $etc = '...', $break_words = false, $middle = false)
{
    $start = 0;
    $cncount=cncount($string);
    if($cncount>($sublen/2)) {
        $sublen=ceil($sublen/2);
    } else {
        $sublen=$sublen-$cncount;
    }

    $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
    preg_match_all($pa, $string, $t_string);
    if(count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen))."...";
    return join('', array_slice($t_string[0], $start, $sublen));
}

function cncount($str)
{
	$len=strlen($str);
	$cncount=0;

	for($i = 0; $i < $len; $i++)
	{
		$temp_str=substr($str,$i,1);
		if(ord($temp_str) > 127)
		{
			$cncount++;
		}
	}

	return ceil($cncount/3);
}
?>