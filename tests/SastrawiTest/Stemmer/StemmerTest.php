<?php

namespace SastrawiTest\Stemmer;

use Sastrawi\Stemmer\Stemmer;
use Sastrawi\Dictionary\ArrayDictionary;

class StemmerTest extends \PHPUnit_Framework_TestCase
{
    protected $dictionary;
    
    protected $stemmer;
    
    public function setUp()
    {
        $this->dictionary = new ArrayDictionary(array('beri'));
        $this->stemmer = new Stemmer($this->dictionary);
    }
    
    /**
     * Test removing inflectional particles lah|kah|tah|pun
     */
    public function testRemoveInflectionalParticle()
    {
        $this->assertEquals('dia', $this->stemmer->removeInflectionalParticle('dialah'));
        $this->assertEquals('benar', $this->stemmer->removeInflectionalParticle('benarkah'));
        $this->assertEquals('apa', $this->stemmer->removeInflectionalParticle('apatah'));
        $this->assertEquals('siapa', $this->stemmer->removeInflectionalParticle('siapapun'));
    }

    /**
     * Test removing inflectional possessive pronoun ku|mu|nya
     */
    public function testRemoveInflectionalPossessivePronoun()
    {
        $this->assertEquals('kemeja', $this->stemmer->removeInflectionalPossessivePronoun('kemejaku'));
        $this->assertEquals('baju', $this->stemmer->removeInflectionalPossessivePronoun('bajumu'));
        $this->assertEquals('celana', $this->stemmer->removeInflectionalPossessivePronoun('celananya'));
    }
    
    /**
     * Test removing derivational suffixes i|kan|an
     */
    public function testRemoveDerivationalSuffix()
    {
        $this->assertEquals('menghantu', $this->stemmer->removeDerivationalSuffix('menghantui'));
        $this->assertEquals('membeli', $this->stemmer->removeDerivationalSuffix('membelikan'));
        $this->assertEquals('penjual', $this->stemmer->removeDerivationalSuffix('penjualan'));
    }

    /**
     * Test get removed affix
     */
    public function testGetRemovedAffix()
    {
        $this->assertEquals('i', $this->stemmer->getRemovedAffix('menghantui', 'menghantu'));
        $this->assertEquals('kan', $this->stemmer->getRemovedAffix('membelikan', 'membeli'));
        $this->assertEquals('an', $this->stemmer->getRemovedAffix('penjualan', 'penjual'));
    }

    /**
     * Test removing plain prefixes di|ke|se
     */
    public function testRemovePlainPrefix()
    {
        $this->assertEquals('buang', $this->stemmer->removePlainPrefix('dibuang'));
        $this->assertEquals('sakitan', $this->stemmer->removePlainPrefix('kesakitan'));
        $this->assertEquals('kuat', $this->stemmer->removePlainPrefix('sekuat'));
    }

    /**
     * Test contains invalid affix pair ber-i|di-an|ke-i|ke-kan|me-an|ter-an|per-an
     */
    public function testContainsInvalidAffixPair()
    {
        $this->assertFalse($this->stemmer->containsInvalidAffixPair('memberikan'));
        $this->assertFalse($this->stemmer->containsInvalidAffixPair('ketahui'));
        
        $this->assertTrue($this->stemmer->containsInvalidAffixPair('berjatuhi'));
        $this->assertTrue($this->stemmer->containsInvalidAffixPair('dipukulan'));
        $this->assertTrue($this->stemmer->containsInvalidAffixPair('ketiduri'));
        $this->assertTrue($this->stemmer->containsInvalidAffixPair('ketidurkan'));
        $this->assertTrue($this->stemmer->containsInvalidAffixPair('menduaan'));
        $this->assertTrue($this->stemmer->containsInvalidAffixPair('terduaan'));
        $this->assertTrue($this->stemmer->containsInvalidAffixPair('perkataan')); // wtf?
    }

    /**
     * Don't stem such a short word (three or fewer characters)
     */
    public function testStemReturnImmediatelyOnShortWord()
    {
        $this->assertEquals('mei', $this->stemmer->stem('mei'));
        $this->assertEquals('bui', $this->stemmer->stem('bui'));
    }

    /**
     * To prevent overstemming : nilai could have been overstemmed to nila
     * if we don't lookup against the dictionary
     */
    public function testStemReturnImmediatelyIfFoundOnDictionary()
    {
        $this->assertEquals('nila', $this->stemmer->stem('nilai'));
        $this->stemmer->getDictionary()->add('nilai');
        $this->assertEquals('nilai', $this->stemmer->stem('nilai'));
    }

    /**
     * Rule 1a : berV -> ber-V 
     */
    public function testDisambiguatePrefixRule1a()
    {
        $this->assertEquals('adu', $this->stemmer->disambiguatePrefixRule1a('beradu'));
    }

    /**
     * Rule 1a : berV -> be-rV 
     */
    public function testDisambiguatePrefixRule1b()
    {
        $this->assertEquals('rambut', $this->stemmer->disambiguatePrefixRule1b('berambut'));
    }

    /**
     * Rule 2 : berCAP -> ber-CAP where C != 'r' AND P != 'er'
     */
    public function testDisambiguatePrefixRule2()
    {
        $this->assertEquals('suara', $this->stemmer->disambiguatePrefixRule2('bersuara'));
    }

    /**
     * Rule 3 : berCAerV -> ber-CAerV where C != 'r'
     */
    public function testDisambiguatePrefixRule3()
    {
        $this->assertEquals('daerah', $this->stemmer->disambiguatePrefixRule3('berdaerah'));
    }

    /**
     * Rule 4 : belajar -> bel-ajar
     */
    public function testDisambiguatePrefixRule4()
    {
        $this->assertEquals('ajar', $this->stemmer->disambiguatePrefixRule4('belajar'));
    }

    /**
     * Rule 5 : beC1erC2 -> be-C1erC2
     */
    public function testDisambiguatePrefixRule5()
    {
        $this->assertEquals('kerja', $this->stemmer->disambiguatePrefixRule5('bekerja'));
        $this->assertEquals('ternak', $this->stemmer->disambiguatePrefixRule5('beternak'));
    }

    /**
     * Rule 6a : terV -> ter-V
     */
    public function testDisambiguatePrefixRule6a()
    {
        $this->assertEquals('asing', $this->stemmer->disambiguatePrefixRule6a('terasing'));
    }

    /**
     * Rule 6b : terV -> te-rV
     */
    public function testDisambiguatePrefixRule6b()
    {
        $this->assertEquals('raup', $this->stemmer->disambiguatePrefixRule6b('teraup'));
    }

    /**
     * Rule 7 : terCerV -> ter-CerV where C != 'r'
     */
    public function testDisambiguatePrefixRule7()
    {
        $this->assertEquals('gerak', $this->stemmer->disambiguatePrefixRule7('tergerak'));
    }

    /**
     * Rule 8 : terCP -> ter-CP where C != 'r' and P != 'er'
     */
    public function testDisambiguatePrefixRule8()
    {
        $this->assertEquals('puruk', $this->stemmer->disambiguatePrefixRule8('terpuruk'));
    }

    /**
     * Rule 9 : teC1erC2 -> te-C1erC2 where C != 'r'
     */
    public function testDisambiguatePrefixRule9()
    {
        $this->assertEquals('terbang', $this->stemmer->disambiguatePrefixRule9('teterbang'));
    }

    /**
     * Rule 10 : me{l|r|w|y}V -> me-{l|r|w|y}V
     */
    public function testDisambiguatePrefixRule10()
    {
        $this->assertEquals('lipat', $this->stemmer->disambiguatePrefixRule10('melipat'));
        $this->assertEquals('rumput', $this->stemmer->disambiguatePrefixRule10('merumput'));
        $this->assertEquals('warna', $this->stemmer->disambiguatePrefixRule10('mewarna'));
        $this->assertEquals('yakin', $this->stemmer->disambiguatePrefixRule10('meyakin'));
    }

    /**
     * Rule 11 : mem{b|f|v} -> mem-{b|f|v}
     */
    public function testDisambiguatePrefixRule11()
    {
        $this->assertEquals('bangun', $this->stemmer->disambiguatePrefixRule11('membangun'));
        $this->assertEquals('fitnah', $this->stemmer->disambiguatePrefixRule11('memfitnah'));
        $this->assertEquals('vonis', $this->stemmer->disambiguatePrefixRule11('memvonis'));
    }

    /**
     * Rule 12 : mempe{r|l} -> mem-pe{r|l}
     */
    public function testDisambiguatePrefixRule12()
    {
        $this->assertEquals('pertinggi', $this->stemmer->disambiguatePrefixRule12('mempertinggi'));
        $this->assertEquals('pelajari', $this->stemmer->disambiguatePrefixRule12('mempelajari'));
    }

    /**
     * Rule 13 : mem{rV|v} -> me-m{rV|V}
     */
    public function testDisambiguatePrefixRule13()
    {
        $this->assertEquals('minum', $this->stemmer->disambiguatePrefixRule13('meminum'));
    }

    /**
     * Rule 14 : men{c|d|j|z} -> men-{c|d|j|z}
     */
    public function testDisambiguatePrefixRule14()
    {
        $this->assertEquals('cinta', $this->stemmer->disambiguatePrefixRule14('mencinta'));
        $this->assertEquals('dua', $this->stemmer->disambiguatePrefixRule14('mendua'));
        $this->assertEquals('jauh', $this->stemmer->disambiguatePrefixRule14('menjauh'));
        $this->assertEquals('ziarah', $this->stemmer->disambiguatePrefixRule14('menziarah'));
    }
}
