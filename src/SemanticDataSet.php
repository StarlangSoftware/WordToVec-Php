<?php

namespace olcaytaner\WordToVec;

use olcaytaner\Dictionary\Dictionary\VectorizedDictionary;

class SemanticDataSet
{
    private array $pairs = [];

    /**
     * Constructor for the semantic dataset. Reads word pairs and their similarity scores from an input file.
     * @param string|null $fileName Input file that stores the word pair and similarity scores.
     */
    public function __construct(?string $fileName = null)
    {
        if ($fileName !== null) {
            $file = fopen($fileName, "r");
            while ($line = fgets($file)) {
                $items = explode(" ", trim($line));
                $this->pairs[] = new WordPair($items[0], $items[1], floatval($items[2]));
            }
            fclose($file);
        }
    }

    /**
     * Calculates the similarities between words in the dataset. The word vectors will be taken from the input
     * vectorized dictionary.
     * @param VectorizedDictionary $dictionary Vectorized dictionary that stores the word vectors.
     * @return SemanticDataSet Word pairs and their calculated similarities stored as a semantic dataset.
     */
    public function calculateSimilarities(VectorizedDictionary $dictionary): SemanticDataSet
    {
        $result = new SemanticDataSet();
        for ($i = 0; $i < count($this->pairs); $i++) {
            $word1 = $this->pairs[$i]->getWord1();
            $word2 = $this->pairs[$i]->getWord2();
            $vectorizedWord1 = $dictionary->getWordWithName($word1);
            $vectorizedWord2 = $dictionary->getWordWithName($word2);
            if ($vectorizedWord1 !== null && $vectorizedWord2 !== null) {
                $similarity = $vectorizedWord1->getVector()->cosineSimilarity($vectorizedWord2->getVector());
                $result->pairs[] = new WordPair($word1, $word2, $similarity);
            } else {
                array_splice($this->pairs, $i, 1);
                $i--;
            }
        }
        return $result;
    }

    static function pairCompareRelatedBy(WordPair $a, WordPair $b): int
    {
        if ($a->getRelatedBy() < $b->getRelatedBy()) {
            return 1;
        } elseif ($a->getRelatedBy() > $b->getRelatedBy()) {
            return -1;
        } else {
            return 0;
        }
    }

    /**
     * Returns the size of the semantic dataset.
     * @return int The size of the semantic dataset.
     */
    public function size(): int
    {
        return count($this->pairs);
    }

    /**
     * Sorts the word pairs in the dataset according to the WordPairComparator.
     */
    public function sort(): void
    {
        usort($this->pairs, [SemanticDataSet::class, "pairCompareRelatedBy"]);
    }

    /**
     * Finds and returns the index of a word pair in the pairs array list. If there is no such word pair, it
     * returns -1.
     * @param WordPair $wordPair Word pair to search in the semantic dataset.
     * @return int Index of the given word pair in the pairs array list. If it does not exist, the method returns -1.
     */
    public function index(WordPair $wordPair): int
    {
        for ($i = 0; $i < count($this->pairs); $i++) {
            if ($wordPair->getWord1() == $this->pairs[$i]->getWord1() && $wordPair->getWord2() == $this->pairs[$i]->getWord2()) {
                return $i;
            }
        }
        return -1;
    }

    /**
     * Calculates the Spearman correlation coefficient with this dataset to the given semantic dataset.
     * @param SemanticDataSet $semanticDataSet Given semantic dataset with which Spearman correlation coefficient is calculated.
     * @return float Spearman correlation coefficient with the given semantic dataset.
     */
    public function spearmanCorrelation(SemanticDataSet $semanticDataSet): float
    {
        $sum = 0;
        $this->sort();
        $semanticDataSet->sort();
        for ($i = 0; $i < count($this->pairs); $i++) {
            $rank1 = $i + 1;
            $rank2 = $semanticDataSet->index($this->pairs[$i]) + 1;
            $di = $rank1 - $rank2;
            $sum += 6 * $di * $di;
        }
        $n = count($this->pairs);
        $ratio = $sum / ($n * ($n * $n - 1));
        return 1 - $ratio;
    }
}