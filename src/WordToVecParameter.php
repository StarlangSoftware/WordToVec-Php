<?php

namespace olcaytaner\WordToVec;

class WordToVecParameter
{
    private int $layerSize = 100;
    private bool $cbow = true;
    private float $alpha = 0.025;
    private int $window = 5;
    private bool $hierarchicalSoftMax = false;
    private int $negativeSampleSize = 5;
    private int $numberOfIterations = 2;
    private int $seed = 1;

    /**
     * Empty constructor for Word2Vec parameter
     */
    public function __construct()
    {

    }

    /**
     * Accessor for layerSize attribute.
     * @return int Size of the word vectors.
     */
    public function getLayerSize(): int
    {
        return $this->layerSize;
    }

    /**
     * Accessor for CBow attribute.
     * @return bool True is CBow will be applied, false otherwise.
     */
    public function isCbow(): bool
    {
        return $this->cbow;
    }

    /**
     * Accessor for the alpha attribute.
     * @return float Current learning rate alpha.
     */
    public function getAlpha(): float
    {
        return $this->alpha;
    }

    /**
     * Accessor for the window size attribute.
     * @return int Current window size.
     */
    public function getWindow(): int
    {
        return $this->window;
    }

    /**
     * Accessor for the hierarchicalSoftMax attribute.
     * @return bool If hierarchical softmax will be applied, returns true; false otherwise.
     */
    public function isHierarchicalSoftMax(): bool
    {
        return $this->hierarchicalSoftMax;
    }

    /**
     * Accessor for the negativeSamplingSize attribute.
     * @return int number of negative samples that will be withdrawn.
     */
    public function getNegativeSampleSize(): int
    {
        return $this->negativeSampleSize;
    }

    /**
     * Accessor for the numberOfIterations attribute.
     * @return int number of epochs to train the network.
     */
    public function getNumberOfIterations(): int
    {
        return $this->numberOfIterations;
    }

    /**
     * Accessor for the seed attribute.
     * @return int Seed to train the network.
     */
    public function getSeed(): int
    {
        return $this->seed;
    }

    /**
     * Mutator for the layerSize attribute.
     * @param int $layerSize New size of the word vectors.
     */
    public function setLayerSize(int $layerSize): void
    {
        $this->layerSize = $layerSize;
    }

    /**
     * Mutator for cBow attribute
     * @param bool $cbow True if CBow applied; false if SkipGram applied.
     */
    public function setCbow(bool $cbow): void
    {
        $this->cbow = $cbow;
    }

    /**
     * Mutator for alpha attribute
     * @param float $alpha New learning rate.
     */
    public function setAlpha(float $alpha): void
    {
        $this->alpha = $alpha;
    }

    /**
     * Mutator for the window size attribute.
     * @param int $window New window size.
     */
    public function setWindow(int $window): void
    {
        $this->window = $window;
    }

    /**
     * Mutator for the hierarchicalSoftMax attribute.
     * @param bool $hierarchicalSoftMax True is hierarchical softMax applied; false otherwise.
     */
    public function setHierarchicalSoftMax(bool $hierarchicalSoftMax): void
    {
        $this->hierarchicalSoftMax = $hierarchicalSoftMax;
    }

    /**
     * Mutator for the negativeSamplingSize attribute.
     * @param int $negativeSamplingSize New number of negative instances that will be withdrawn.
     */
    public function setNegativeSampleSize(int $negativeSampleSize): void
    {
        $this->negativeSampleSize = $negativeSampleSize;
    }

    /**
     * Mutator for the numberOfIterations attribute.
     * @param int $numberOfIterations New number of iterations.
     */
    public function setNumberOfIterations(int $numberOfIterations): void
    {
        $this->numberOfIterations = $numberOfIterations;
    }

    /**
     * Mutator for the seed attribute.
     * @param int $seed New number of seed.
     */
    public function setSeed(int $seed): void
    {
        $this->seed = $seed;
    }
}