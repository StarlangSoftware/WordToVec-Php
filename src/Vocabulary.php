<?php

namespace olcaytaner\WordToVec;

use olcaytaner\Corpus\Corpus;
use olcaytaner\Dictionary\Dictionary\Word;

class Vocabulary
{
    private array $vocabulary = [];
    private array $table = [];
    private array $wordMap = [];

    /**
     * Constructor for the {@link Vocabulary} class. For each distinct word in the corpus, a {@link VocabularyWord}
     * instance is created. After that, words are sorted according to their occurrences. Unigram table is constructed,
     * where after Huffman tree is created based on the number of occurrences of the words.
     * @param Corpus $corpus Corpus used to train word vectors using Word2Vec algorithm.
     */
    public function __construct(Corpus $corpus)
    {
        $wordList = $corpus->getWordList();
        foreach ($wordList as $word) {
            $this->vocabulary[] = new VocabularyWord($word, $corpus->getCount(new Word($word)));
        }
        usort($this->vocabulary, [Vocabulary::class, "wordCompareCount"]);
        $this->createUniGramTable();
        $this->constructHuffmanTree();
        usort($this->vocabulary, [Vocabulary::class, "wordCompareText"]);
        for ($i = 0; $i < count($this->vocabulary); $i++) {
            $this->wordMap[$this->vocabulary[$i]->getName()] = $i;
        }
    }

    static function wordCompareCount(VocabularyWord $word1, VocabularyWord $word2): int
    {
        if ($word1->getCount() > $word2->getCount()) {
            return 1;
        } elseif ($word1->getCount() < $word2->getCount()) {
            return -1;
        } else {
            return 0;
        }
    }

    static function wordCompareText(VocabularyWord $word1, VocabularyWord $word2): int
    {
        return strcmp($word1->getName(), $word2->getName());
    }

    /**
     * Returns number of words in the vocabulary.
     * @return int Number of words in the vocabulary.
     */
    public function size(): int
    {
        return count($this->vocabulary);
    }

    /**
     * Searches a word and returns the position of that word in the vocabulary. Search is done using binary search.
     * @param Word $word Word to be searched.
     * @return int Position of the word searched.
     */
    public function getPosition(Word $word): int
    {
        return $this->wordMap[$word->getName()];
    }

    /**
     * Returns the word at a given index.
     * @param int $index Index of the word.
     * @return VocabularyWord The word at a given index.
     */
    public function getWord(int $index): VocabularyWord
    {
        return $this->vocabulary[$index];
    }

    /**
     * Constructs Huffman Tree based on the number of occurences of the words.
     */
    private function constructHuffmanTree(): void
    {
        $count = [];
        $code = array_fill(0, VocabularyWord::$MAX_CODE_LENGTH, 0);
        $point = array_fill(0, VocabularyWord::$MAX_CODE_LENGTH, 0);
        $binary = array_fill(0, count($this->vocabulary) * 2 + 1, 0);
        $parentNode = array_fill(0, count($this->vocabulary) * 2 + 1, 0);
        for ($a = 0; $a < count($this->vocabulary); $a++) {
            $count[] = $this->vocabulary[$a]->getCount();
        }
        for ($a = count($this->vocabulary); $a < 2 * count($this->vocabulary); $a++) {
            $count[] = 1000000000;
        }
        $pos1 = count($this->vocabulary) - 1;
        $pos2 = count($this->vocabulary);
        for ($a = 0; $a < count($this->vocabulary) - 1; $a++) {
            if ($pos1 >= 0) {
                if ($count[$pos1] < $count[$pos2]) {
                    $min1i = $pos1;
                    $pos1--;
                } else {
                    $min1i = $pos2;
                    $pos2--;
                }
            } else {
                $min1i = $pos2;
                $pos2++;
            }
            if ($pos1 >= 0) {
                if ($count[$pos1] < $count[$pos2]) {
                    $min2i = $pos1;
                    $pos1--;
                } else {
                    $min2i = $pos2;
                    $pos2--;
                }
            } else {
                $min2i = $pos2;
                $pos2++;
            }
            $count[count($this->vocabulary) + $a] = $count[$min1i] + $count[$min2i];
            $parentNode[$min1i] = count($this->vocabulary) + $a;
            $parentNode[$min2i] = count($this->vocabulary) + $a;
            $binary[$min2i] = 1;
        }
        for ($a = 0; $a < count($this->vocabulary); $a++) {
            $b = $a;
            $i = 0;
            while (true) {
                $code[$i] = $binary[$b];
                $point[$i] = $b;
                $i++;
                $b = $parentNode[$b];
                if ($b == count($this->vocabulary) * 2 - 2) {
                    break;
                }
            }
            $this->vocabulary[$a]->setCodeLength($i);
            $this->vocabulary[$a]->setPoint(0, count($this->vocabulary) - 2);
            for ($b = 0; $b < $i; $b++) {
                $this->vocabulary[$a]->setCode($i - $b - 1, $code[$b]);
                $this->vocabulary[$a]->setPoint($i - $b, $point[$b] - count($this->vocabulary));
            }
        }
    }

    /**
     * Constructs the unigram table based on the number of occurences of the words.
     */
    private function createUniGramTable(): void
    {
        $total = 0;
        foreach ($this->vocabulary as $vocabularyWord) {
            $total += pow($vocabularyWord->getCount(), 0.75);
        }
        $i = 0;
        $d1 = pow($this->vocabulary[$i]->getCount(), 0.75) / $total;
        for ($a = 0; $a < count($this->vocabulary); $a++) {
            $this->table[] = $i;
            if ($a / (2 * count($this->vocabulary)) > $d1) {
                $i++;
                $d1 += pow($this->vocabulary[$i]->getCount(), 0.75) / $total;
            }
            if ($i >= count($this->vocabulary)) {
                $i = count($this->vocabulary) - 1;
            }
        }
    }

    /**
     * Accessor for the unigram table.
     * @param int $index Index of the word.
     * @return int Unigram table value at a given index.
     */
    public function getTableValue(int $index): int
    {
        return $this->table[$index];
    }

    /**
     * Returns size of the unigram table.
     * @return int Size of the unigram table.
     */
    public function getTableSize(): int{
        return count($this->table);
    }
}