<?php

namespace Microflex\Utils;

class Security
{
    public function sanitize($value)
    {
    	if ($value === null) return null;

        if ( is_array($value) ) {

        	$new = [];

            foreach ($value as $k => $v) {

                if ( is_array($v) ) {

                    $new[$k] = $this->sanitize($v);
                }
                else {
                
                    $new[$k] = htmlspecialchars($v);
                }
            }

            return $new;
        }
        
        return htmlspecialchars($value);
    }
}