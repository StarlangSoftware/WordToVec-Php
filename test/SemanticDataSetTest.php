<?php

use olcaytaner\WordToVec\SemanticDataSet;

class SemanticDataSetTest extends \PHPUnit\Framework\TestCase
{
    public function testSpearman()
    {
        $semanticDataSet = new SemanticDataSet("../AnlamverRel.txt");
        $this->assertEquals(1.0, $semanticDataSet->spearmanCorrelation($semanticDataSet));
        $semanticDataSet = new SemanticDataSet("../MC.txt");
        $this->assertEquals(1.0, $semanticDataSet->spearmanCorrelation($semanticDataSet));
        $semanticDataSet = new SemanticDataSet("../MEN.txt");
        $this->assertEquals(1.0, $semanticDataSet->spearmanCorrelation($semanticDataSet));
        $semanticDataSet = new SemanticDataSet("../MTurk771.txt");
        $this->assertEquals(1.0, $semanticDataSet->spearmanCorrelation($semanticDataSet));
        $semanticDataSet = new SemanticDataSet("../RareWords.txt");
        $this->assertEquals(1.0, $semanticDataSet->spearmanCorrelation($semanticDataSet));
        $semanticDataSet = new SemanticDataSet("../RG.txt");
        $this->assertEquals(1.0, $semanticDataSet->spearmanCorrelation($semanticDataSet));
    }
}