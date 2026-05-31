Word Embeddings
============

Distributed representations (DR) of words (i.e., word embeddings) are used to capture semantic and syntactic regularities of the language by analyzing distributions of word relations within the textual data. Modeling methods generating DRs rely on the assumption that 'words that occur in similar contexts tend to have similar meanings' (distributional hypothesis) which stems from the nature of language itself. Due to their unsupervised nature, these modeling methods do not require any human judgement input to train, which allows researchers to train very large datasets in relatively low costs.

Traditional representations of words (i.e., one-hot vectors) are based on word-word (W x W) co-occurrence sparse matrices where W is the number of distinct words in the corpus. On the other hand, distributed word representations (DRs) (i.e., word embeddings) are word-context (W x C) dense matrices where C < W and C is the number of context dimensions which are determined by underlying model assumptions. Dense representations are arguably better at capturing generalized information and more resistant to overfitting due to context vectors representing shared properties of words. DRs are real valued vectors where each context can be considered as a continuous feature of a word. Due to their ability to represent abstract features of a word, DRs are considered as reusable across higher level tasks in ease, even if they are trained with totally different datasets.

Prediction based DR models gained much attention after Mikolov et al.’s neural network based SkipGram model in 2013. The secret behind the prediction based models is simple: never build a sparse matrix at all. Prediction based models construct dense matrix representations directly instead of reducing sparse ones to dense ones. These models are trained like any other supervised learning task by giving lots of positive and negative samples without adding any human supervision costs. Aim of these models is to maximize the probability of each context c with the same distributional assumptions on word-context co-occurrences, similar to count based models.

SkipGram is a prediction based distributional semantic model (DSM) consisting of a shallow neural network architecture inspired from neural language modeling (LM) intuitions. It is commonly known for its open-source implementation library word2vec. SkipGram acts like a log-linear classifier maximizing the prediction of the surrounding words of a word within a context (center window). Probabilistic word and sentence prediction by local neighbors of a word has been successfully applied on LM tasks under Markov assumption. SkipGram leverages the same idea by considering the words within the window as positive and negative instances and learning weights (for k contexts) which maximizes word predictions. In the training process, each word vector starts as a random vector, and then iteratively shifts to the neighboring vector.

For Developers
============

You can also see [Java](https://github.com/starlangsoftware/WordToVec), [Python](https://github.com/starlangsoftware/WordToVec-Py), [Cython](https://github.com/starlangsoftware/WordToVec-Cy), [Js](https://github.com/starlangsoftware/WordToVec-Js), [C#](https://github.com/starlangsoftware/WordToVec-CS), [Swift](https://github.com/starlangsoftware/WordToVec-Swift), [C](https://github.com/starlangsoftware/WordToVec-C), or [C++](https://github.com/starlangsoftware/WordToVec-CPP) repository.

## Requirements

* [Php 8.4 or higher](#php)
* [Git](#git)

### Php 

To check if you have a compatible version of Php installed, use the following command:

    php -V
    
You can find the latest version of Php [here](https://www.php.net/downloads/).

### Git

Install the [latest version of Git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git).

## Download Code

In order to work on code, create a fork from GitHub page. 
Use Git for cloning the code to your local or below line for Ubuntu:

	git clone <your-fork-git-link>

A directory called WordToVec will be created. Or you can use below link for exploring the code:

	git clone https://github.com/starlangsoftware/WordToVec-Php.git

## Open project with PhpStorm IDE

Steps for opening the cloned project:

* Start IDE
* Select **File | Open** from main menu
* Choose `WordToVec-Php` file
* Select open as project option
* Couple of seconds, dependencies will be downloaded.

For Contibutors
============

### composer.json file

1. autoload is important when this package will be imported.
```
  "autoload": {
    "psr-4": {
      "olcaytaner\\WordNet\\": "src/"
    }
  },
```
2. Dependencies should be maximum (not only direct but also indirect references should also be given), everything directly in the code should be given here.
```
  "require-dev": {
    "phpunit/phpunit": "11.4.0",
    "olcaytaner/dictionary": "1.0.0",
    "olcaytaner/xmlparser": "1.0.1",
    "olcaytaner/morphologicalanalysis": "1.0.0"
  }
```

### Data files
1. Add data files to the project folder. Subprojects should include all data files of the parent projects.

### Php files

1. Do not forget to comment each function.
```
    /**
     * Returns true if specified semantic relation type presents in the relations list.
     *
     * @param SemanticRelationType $relationType element whose presence in the list is to be tested
     * @return bool true if specified semantic relation type presents in the relations list
     */
    public function containsRelationType(SemanticRelationType $relationType): bool{
        foreach ($this->relations as $relation){
            if ($relation instanceof SematicRelation && $relation->getRelationType() == $relationType){
                return true;
            }
        }
        return false;
    }
```
2. Function names should follow caml case.
```
    public function getRelation(int $index): Relation{
```
3. Write getter and setter methods.
```
    public function getOrigin(): ?string
    public function setName(string $name): void
```
4. Use standard javascript test style by extending the TestCase class. Use setup when necessary.
```
class WordNetTest extends TestCase
{
    private WordNet $turkish;

    protected function setUp(): void
    {
        ini_set('memory_limit', '450M');
        $this->turkish = new WordNet();
    }

    public function testSize()
    {
        $this->assertEquals(78327, $this->turkish->size());
    }
```
5. Enumerated types should be declared with enum.
```
enum CategoryType
{
    case MATHEMATICS;
    case SPORT;
    case MUSIC;
    case SLANG;
    case BOTANIC;
```
6. If there are multiple constructors for a class, define them as constructor1, constructor2, ..., then from the original constructor call these methods.
```
    public function constructor1(string $path, string $fileName): void
    public function constructor2(string $path, string $extension, int $index): void
    public function __construct(string $path, string $extension, ?int $index = null)
```
7. Use __toString method if necessary to create strings from objects.
```
    public function __toString(): string
```
8. Use xmlparser package for parsing xml files.
```
  $doc = new XmlDocument("../test.xml");
  $doc->parse();
  $root = $doc->getFirstChild();
  $firstChild = $root->getFirstChild();
```
