<?php

namespace olcaytaner\WordToVec;

use olcaytaner\Corpus\Corpus;
use olcaytaner\Dictionary\Dictionary\VectorizedDictionary;
use olcaytaner\Dictionary\Dictionary\VectorizedWord;
use olcaytaner\Dictionary\Dictionary\WordComparator;
use olcaytaner\Math\Matrix;
use olcaytaner\Math\Vector;

class NeuralNetwork
{
    private Matrix $wordVectors;
    private Matrix $wordVectorUpdate;
    private Vocabulary $vocabulary;
    private WordToVecParameter $parameter;
    private Corpus $corpus;
    private array $expTable = [];
    private static int $EXP_TABLE_SIZE = 1000;
    private static int $MAX_EXP = 6;

    /**
     * Constructor for the {@link NeuralNetwork} class. Gets corpus and network parameters as input and sets the
     * corresponding parameters first. After that, initializes the network with random weights between -0.5 and 0.5.
     * Constructs vector update matrix and prepares the exp table.
     * @param Corpus $corpus Corpus used to train word vectors using Word2Vec algorithm.
     * @param WordToVecParameter $parameter Parameters of the Word2Vec algorithm.
     */
    public function __construct(Corpus $corpus, WordToVecParameter $parameter)
    {
        $this->vocabulary = new Vocabulary($corpus);
        $this->parameter = $parameter;
        $this->corpus = $corpus;
        $this->wordVectors = new Matrix($this->vocabulary->size(), $parameter->getLayerSize(), -0.5, 0.5);
        $this->wordVectorUpdate = new Matrix($this->vocabulary->size(), $parameter->getLayerSize());
        $this->prepareExpTable();
    }

    public function vocabularySize(): int
    {
        return $this->vocabulary->size();
    }

    /**
     * Constructs the fast exponentiation table. Instead of taking exponent at each time, the algorithm will lookup
     * the table.
     */
    private function prepareExpTable(): void
    {
        for ($i = 0; $i < NeuralNetwork::$EXP_TABLE_SIZE; $i++) {
            $this->expTable[] = exp(($i / NeuralNetwork::$EXP_TABLE_SIZE * 2 - 1) * NeuralNetwork::$MAX_EXP);
            $this->expTable[$i] /= ($this->expTable[$i] + 1);
        }
    }

    /**
     * Main method for training the Word2Vec algorithm. Depending on the training parameter, CBox or SkipGram algorithm
     * is applied.
     * @return VectorizedDictionary Dictionary of word vectors.
     */
    public function train(): VectorizedDictionary
    {
        $result = new VectorizedDictionary(WordComparator::TURKISH);
        if ($this->parameter->isCbow()) {
            $this->trainCbow();
        } else {
            $this->trainSkipGram();
        }
        for ($i = 0; $i < $this->vocabulary->size(); $i++) {
            $result->addWord(new VectorizedWord($this->vocabulary->getWord($i)->getName(), $this->wordVectors->getRowVector($i)));
        }
        return $result;
    }

    /**
     * Calculates G value in the Word2Vec algorithm.
     * @param float $f F value.
     * @param float $alpha Learning rate alpha.
     * @param int $label Label of the instance.
     * @return float Calculated G value.
     */
    private function calculateG(float $f, float $alpha, int $label): float
    {
        if ($f > NeuralNetwork::$MAX_EXP) {
            return ($label - 1) * $alpha;
        } else {
            if ($f < NeuralNetwork::$MAX_EXP) {
                return $label * $alpha;
            } else {
                return ($label - $this->expTable[floor(($f + NeuralNetwork::$MAX_EXP) *
                            (NeuralNetwork::$EXP_TABLE_SIZE / NeuralNetwork::$MAX_EXP / 2))]) * $alpha;
            }
        }
    }

    /**
     * Main method for training the CBow version of Word2Vec algorithm.
     */
    private function trainCBow(): void
    {
        $iteration = new Iteration($this->corpus, $this->parameter);
        $currentSentence = $this->corpus->getSentence($iteration->getSentenceIndex());
        $outputs = new Vector($this->parameter->getLayerSize(), 0);
        $outputUpdate = new Vector($this->parameter->getLayerSize(), 0);
        while ($iteration->getIterationCount() < $this->parameter->getNumberOfIterations()) {
            $iteration->alphaUpdate();
            $word = $currentSentence->getWord($iteration->getSentencePosition());
            $wordIndex = $this->vocabulary->getPosition($word);
            $currentWord = $this->vocabulary->getWord($wordIndex);
            $outputs->clear();
            $outputUpdate->clear();
            $b = floor((mt_rand() / mt_getrandmax()) * $this->parameter->getWindow());
            $cw = 0;
            for ($a = $b; $a < $this->parameter->getWindow() * 2 + 1 - $b; $a++){
                $c = $iteration->getSentencePosition() - $this->parameter->getWindow() + $a;
                if ($a != $this->parameter->getWindow() && $currentSentence->safeIndex($c)) {
                    $lastWordIndex = $this->vocabulary->getPosition($currentSentence->getWord($c));
                    $outputs->addVector($this->wordVectors->getRowVector($lastWordIndex));
                    $cw++;
                }
            }
            if ($cw > 0) {
                $outputs->divide($cw);
                if ($this->parameter->isHierarchicalSoftMax()){
                    for ($d = 0; $d < $currentWord->getCodeLength(); $d++) {
                        $l2 = $currentWord->getPoint($d);
                        $f = $outputs->dotProduct($this->wordVectorUpdate->getRowVector($l2));
                        if ($f <= -NeuralNetwork::$MAX_EXP || $f >= NeuralNetwork::$MAX_EXP){
                            continue;
                        } else{
                            $f = $this->expTable[floor(($f + NeuralNetwork::$MAX_EXP) *
                                    (NeuralNetwork::$EXP_TABLE_SIZE / NeuralNetwork::$MAX_EXP / 2))];
                        }
                        $g = (1 - $currentWord->getCode($d) - $f) * $iteration->getAlpha();
                        $outputUpdate->addVector($this->wordVectorUpdate->getRowVector($l2)->product($g));
                        $this->wordVectorUpdate->add($l2, $outputs->product($g));
                    }
                } else {
                    for ($d = 0; $d < $this->parameter->getNegativeSampleSize() + 1; $d++) {
                        if ($d == 0) {
                            $target = $wordIndex;
                            $label = 1;
                        } else {
                            $target = $this->vocabulary->getTableValue(floor((mt_rand() / mt_getrandmax()) * $this->vocabulary->getTableSize()));
                            if ($target == 0)
                                $target = floor((mt_rand() / mt_getrandmax()) * ($this->vocabulary->size() - 1)) + 1;
                            if ($target == $wordIndex)
                                continue;
                            $label = 0;
                        }
                        $l2 = $target;
                        $f = $outputs->dotProduct($this->wordVectorUpdate->getRowVector($l2));
                        $g = $this->calculateG($f, $iteration->getAlpha(), $label);
                        $outputUpdate->addVector($this->wordVectorUpdate->getRowVector($l2)->product($g));
                        $this->wordVectorUpdate->add($l2, $outputs->product($g));
                    }
                }
                for ($a = $b; $a < $this->parameter->getWindow() * 2 + 1 - $b; $a++){
                    $c = $iteration->getSentencePosition() - $this->parameter->getWindow() + $a;
                    if ($a != $this->parameter->getWindow() && $currentSentence->safeIndex($c)) {
                        $lastWordIndex = $this->vocabulary->getPosition($currentSentence->getWord($c));
                        $this->wordVectors->add($lastWordIndex, $outputUpdate);
                    }
                }
            }
            $currentSentence = $iteration->sentenceUpdate($currentSentence);
        }
    }

    /**
     * Main method for training the SkipGram version of Word2Vec algorithm.
     */
    private function trainSkipGram(): void{
        $iteration = new Iteration($this->corpus, $this->parameter);
        $currentSentence = $this->corpus->getSentence($iteration->getSentenceIndex());
        $outputUpdate = new Vector($this->parameter->getLayerSize(), 0);
        while ($iteration->getIterationCount() < $this->parameter->getNumberOfIterations()) {
            $iteration->alphaUpdate();
            $word = $currentSentence->getWord($iteration->getSentencePosition());
            $wordIndex = $this->vocabulary->getPosition($word);
            $currentWord = $this->vocabulary->getWord($wordIndex);
            $outputUpdate->clear();
            $b = floor((mt_rand() / mt_getrandmax()) * $this->parameter->getWindow());
            for ($a = $b; $a < $this->parameter->getWindow() * 2 + 1 - $b; $a++) {
                $c = $iteration->getSentencePosition() - $this->parameter->getWindow() + $a;
                if ($a != $this->parameter->getWindow() && $currentSentence->safeIndex($c)) {
                    $l1 = $this->vocabulary->getPosition($currentSentence->getWord($c));
                    $outputUpdate->clear();
                    if ($this->parameter->isHierarchicalSoftMax()) {
                        for ($d = 0; $d < $currentWord->getCodeLength(); $d++) {
                            $l2 = $currentWord->getPoint($d);
                            $f = $this->wordVectors->getRowVector($l1)->dotProduct($this->wordVectorUpdate->getRowVector($l2));
                            if ($f <= -NeuralNetwork::$MAX_EXP || $f >= NeuralNetwork::$MAX_EXP){
                                continue;
                            } else{
                                $f = $this->expTable[floor(($f + NeuralNetwork::$MAX_EXP) *
                                        (NeuralNetwork::$EXP_TABLE_SIZE / NeuralNetwork::$MAX_EXP / 2))];
                            }
                            $g = (1 - $currentWord->getCode($d) - $f) * $iteration->getAlpha();
                            $outputUpdate->addVector($this->wordVectorUpdate->getRowVector($l2)->product($g));
                            $this->wordVectorUpdate->add($l2, $this->wordVectors->getRowVector($l1)->product($g));
                        }
                    } else {
                        for ($d = 0; $d < $this->parameter->getNegativeSampleSize() + 1; $d++) {
                            if ($d == 0) {
                                $target = $wordIndex;
                                $label = 1;
                            } else {
                                $target = $this->vocabulary->getTableValue(floor((mt_rand() / mt_getrandmax()) * $this->vocabulary->getTableSize()));
                                if ($target == 0)
                                    $target = floor((mt_rand() / mt_getrandmax()) * ($this->vocabulary->size() - 1)) + 1;
                                if ($target == $wordIndex)
                                    continue;
                                $label = 0;
                            }
                            $l2 = $target;
                            $f = $this->wordVectors->getRowVector($l1)->dotProduct($this->wordVectorUpdate->getRowVector($l2));
                            $g = $this->calculateG($f, $iteration->getAlpha(), $label);
                            $outputUpdate->addVector($this->wordVectorUpdate->getRowVector($l2)->product($g));
                            $this->wordVectorUpdate->add($l2, $this->wordVectors->getRowVector($l1)->product($g));
                        }
                    }
                    $this->wordVectors->add($l1, $outputUpdate);
                }
            }
            $currentSentence = $iteration->sentenceUpdate($currentSentence);
        }
    }
}