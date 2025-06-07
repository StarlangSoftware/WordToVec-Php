<?php

namespace olcaytaner\WordToVec;

use olcaytaner\Dictionary\Dictionary\Word;

class VocabularyWord extends Word
{
    private int $count;
    private array $code;
    private array $point;
    private int $codeLength;
    static int $MAX_CODE_LENGTH = 40;

    /**
     * Constructor for a {@link VocabularyWord}. The constructor gets name and count values and sets the corresponding
     * attributes. It also initializes the code and point arrays for this word.
     * @param string $name Lemma of the word
     * @param int $count Number of occurrences of this word in the corpus
     */
    public function __construct(string $name, int $count){
        parent::__construct($name);
        $this->count = $count;
        $this->code = array_fill(0, self::$MAX_CODE_LENGTH, 0);
        $this->point = array_fill(0, self::$MAX_CODE_LENGTH, 0);
        $this->codeLength = 0;
    }

    /**
     * Accessor for the count attribute.
     * @return int Number of occurrences of this word.
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * Mutator for codeLength attribute.
     * @param int $codeLength New value for the codeLength.
     */
    public function setCodeLength(int $codeLength): void
    {
        $this->codeLength = $codeLength;
    }

    /**
     * Mutator for code attribute.
     * @param int $index Index of the code
     * @param int $value New value for that indexed element of code.
     */
    public function setCode(int $index, int $value): void
    {
        $this->code[$index] = $value;
    }

    /**
     * Mutator for point attribute.
     * @param int $index Index of the point
     * @param int $value New value for that indexed element of point.
     */
    public function setPoint(int $index, int $value): void
    {
        $this->point[$index] = $value;
    }

    /**
     * Accessor for the codeLength attribute.
     * @return int Length of the Huffman code for this word.
     */
    public function getCodeLength(): int
    {
        return $this->codeLength;
    }

    /**
     * Accessor for point attribute.
     * @param int $index Index of the point.
     * @return int Value for that indexed element of point.
     */
    public function getPoint(int $index): int
    {
        return $this->point[$index];
    }

    /**
     * Accessor for code attribute.
     * @param int $index Index of the code.
     * @return int Value for that indexed element of code.
     */
    public function getCode(int $index): int
    {
        return $this->code[$index];
    }

}