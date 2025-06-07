<?php

namespace olcaytaner\WordToVec;

class WordPair
{
    private string $word1;
    private string $word2;
    private float $relatedBy;

    /**
     * Constructor of the WordPair object. WordPair stores the information about two words and their similarity scores.
     * @param string $word1 First word
     * @param string $word2 Second word
     * @param float $relatedBy Similarity score between first and second word.
     */
    public function __construct(string $word1, string $word2, float $relatedBy){
        $this->word1 = $word1;
        $this->word2 = $word2;
        $this->relatedBy = $relatedBy;
    }

    /**
     * Accessor for the first word.
     * @return string First word.
     */
    public function getWord1(): string
    {
        return $this->word1;
    }

    /**
     * Accessor for the second word.
     * @return string Second word.
     */
    public function getWord2(): string
    {
        return $this->word2;
    }

    /**
     * Accessor for the similarity score.
     * @return float Similarity score.
     */
    public function getRelatedBy(): float
    {
        return $this->relatedBy;
    }

    /**
     * Mutator for the similarity score.
     * @param float $relatedBy New similarity score
     */
    public function setRelatedBy(float $relatedBy): void
    {
        $this->relatedBy = $relatedBy;
    }

}