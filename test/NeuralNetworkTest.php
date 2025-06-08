<?php

use olcaytaner\Corpus\Corpus;
use olcaytaner\Dictionary\Dictionary\VectorizedDictionary;
use olcaytaner\Dictionary\Dictionary\WordComparator;
use olcaytaner\WordToVec\NeuralNetwork;
use olcaytaner\WordToVec\SemanticDataSet;
use olcaytaner\WordToVec\WordToVecParameter;
use PHPUnit\Framework\TestCase;

class NeuralNetworkTest extends TestCase
{
    public function testTrainEnglishCBow()
    {
        ini_set('memory_limit', '500M');
        $english = new Corpus("../english-xs.txt");
        $mc = new SemanticDataSet("../MC.txt");
        $rg = new SemanticDataSet("../RG.txt");
        $ws = new SemanticDataSet("../WS353.txt");
        $men = new SemanticDataSet("../MEN.txt");
        $mturk = new SemanticDataSet("../MTurk771.txt");
        $rare = new SemanticDataSet("../RareWords.txt");
        $parameter = new WordToVecParameter();
        $parameter->setCbow(true);
        $neuralNetwork = new NeuralNetwork($english, $parameter);
        $dictionary = $neuralNetwork->train();
    }

    public function testWithWordVectors(){
        $dictionary = new VectorizedDictionary(WordComparator::ENGLISH, "../vectors-english-xs.txt");
        $mc = new SemanticDataSet("../MC.txt");
        $rg = new SemanticDataSet("../RG.txt");
        $ws = new SemanticDataSet("../WS353.txt");
        $men = new SemanticDataSet("../MEN.txt");
        $mturk = new SemanticDataSet("../MTurk771.txt");
        $rare = new SemanticDataSet("../RareWords.txt");
        $mc2 = $mc->calculateSimilarities($dictionary);
        print $mc->spearmanCorrelation($mc2) . "\n";
        $rg2 = $rg->calculateSimilarities($dictionary);
        print $rg->spearmanCorrelation($rg2) . "\n";
        $ws2 = $ws->calculateSimilarities($dictionary);
        print $ws->spearmanCorrelation($ws2) . "\n";
        $men2 = $men->calculateSimilarities($dictionary);
        print $men->spearmanCorrelation($men2) . "\n";
        $mturk2 = $mturk->calculateSimilarities($dictionary);
        print $mturk->spearmanCorrelation($mturk2) . "\n";
        $rare2 = $rare->calculateSimilarities($dictionary);
        print $rare->spearmanCorrelation($rare2) . "\n";
    }
}