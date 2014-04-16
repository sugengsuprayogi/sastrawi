<?php

namespace Sastrawi\Morphology\Disambiguator;

/**
 * Disambiguate Prefix Rule 12
 * Nazief and Adriani Rule 12 : mempe{r|l} -> mem-pe{r|l}
 * Modified by Jelita Asian's CS Rule 12 : mempe -> mem-pe
 */
class DisambiguatorPrefixRule12 implements DisambiguatorInterface
{
    /**
     * Disambiguate Prefix Rule 12
     * Nazief and Adriani Original Rule 12 : mempe{r|l} -> mem-pe{r|l}
     * Modified by Jelita Asian's CS Rule 12 : mempe -> mem-pe
     */
    public function disambiguate($word)
    {
        $matches  = null;
        $contains = preg_match('/^mempe(.*)$/', $word, $matches);

        if ($contains === 1) {
            return 'pe' . $matches[1];
        }
    }
}
