<?php

namespace Microflex\Utils;

class Url
{
    public function splitUri($uri)
    {
        $validSlash = true;
        $splitedUri = [];
        $slashPositions = [];
        $uriLength = strlen($uri);

        for ($i=0; $i < $uriLength; $i++) {

            if ($uri[$i] === '?') {

                break;
            }
            elseif ($uri[$i] === '<') {

                $validSlash = false;
            }
            elseif ($uri[$i] === '>') {

                $validSlash = true;
            }
            elseif ($uri[$i] === '/' && $validSlash) {

                $slashPositions[] = $i;
            }
        }

        foreach ($slashPositions as $key => $value) {
            
            $from = ($key === 0) ? 0 : ($slashPositions[$key - 1] + 1);

            $splitedUri[] = substr($uri, $from, $value - $from);
        }

        $splitedUri[] = substr($uri, $value + 1);
        
        return $splitedUri;
    }
}