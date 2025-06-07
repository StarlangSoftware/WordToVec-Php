<?php

namespace olcaytaner\WordToVec;

use olcaytaner\Corpus\Corpus;
use olcaytaner\Corpus\Sentence;

class Iteration
{
    private int $wordCount = 0;
    private int $lastWordCount = 0;
    private int $wordCountActual = 0;
    private int $iterationCount = 0;
    private int $sentencePosition = 0;
    private int $sentenceIndex = 0;
    private float $startingAlpha;
    private float $alpha;
    private Corpus $corpus;
    private WordToVecParameter $wordToVecParameter;

    /**
     * Constructor for the {@link Iteration} class. Get corpus and parameter as input, sets the corresponding
     * parameters.
     * @param Corpus $corpus Corpus used to train word vectors using Word2Vec algorithm.
     * @param WordToVecParameter $wordToVecParameter Parameters of the Word2Vec algorithm.
     */
    public function __construct(Corpus $corpus, WordToVecParameter $wordToVecParameter){
        $this->corpus = $corpus;
        $this->wordToVecParameter = $wordToVecParameter;
        $this->startingAlpha = $wordToVecParameter->getAlpha();
        $this->alpha = $wordToVecParameter->getAlpha();
    }

    /**
     * Accessor for the alpha attribute.
     * @return float $Alpha attribute.
     */
    public function getAlpha(): float
    {
        return $this->alpha;
    }

    /**
     * Accessor for the iterationCount attribute.
     * @return int IterationCount attribute.
     */
    public function getIterationCount(): int
    {
        return $this->iterationCount;
    }

    /**
     * Accessor for the sentenceIndex attribute.
     * @return int SentenceIndex attribute
     */
    public function getSentenceIndex(): int
    {
        return $this->sentenceIndex;
    }

    /**
     * Accessor for the sentencePosition attribute.
     * @return int SentencePosition attribute
     */
    public function getSentencePosition(): int
    {
        return $this->sentencePosition;
    }

    /**
     * Updates the alpha parameter after 10000 words has been processed.
     */
    public function alphaUpdate(): void{
        if ($this->wordCount - $this->lastWordCount > 10000) {
            $this->wordCountActual += $this->wordCount - $this->lastWordCount;
            $this->lastWordCount = $this->wordCount;
            $this->alpha = $this->startingAlpha * (1 - $this->wordCountActual /
                    ($this->wordToVecParameter->getNumberOfIterations() * $this->corpus->numberOfWords() + 1.0));
            if ($this->alpha < $this->startingAlpha * 0.0001)
                $this->alpha = $this->startingAlpha * 0.0001;
        }
    }

    /**
     * Updates sentencePosition, sentenceIndex (if needed) and returns the current sentence processed. If one sentence
     * is finished, the position shows the beginning of the next sentence and sentenceIndex is incremented. If the
     * current sentence is the last sentence, the system shuffles the sentences and returns the first sentence.
     * @param Sentence $currentSentence Current sentence processed.
     * @return Sentence If current sentence is not changed, currentSentence; if changed the next sentence; if next sentence is
     * the last sentence; shuffles the corpus and returns the first sentence.
     */
    public function sentenceUpdate(Sentence $currentSentence): Sentence{
        $this->sentencePosition++;
        if ($this->sentencePosition >= $currentSentence->wordCount()) {
            $this->wordCount += $currentSentence->wordCount();
            $this->sentenceIndex++;
            $this->sentencePosition = 0;
            if ($this->sentenceIndex == $this->corpus->sentenceCount()){
                $this->iterationCount++;
                $this->wordCount = 0;
                $this->lastWordCount = 0;
                $this->sentenceIndex = 0;
            }
            return $this->corpus->getSentence($this->sentenceIndex);
        }
        return $currentSentence;
    }
}