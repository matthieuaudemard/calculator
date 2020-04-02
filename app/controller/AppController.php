<?php


namespace App\Controller;

use App\Model\CalculatorManager;
use Exception;

/**
 * Class AppController
 * @package App\Controller
 */
class AppController
{
    /**
     * @var CalculatorManager
     */
    private $manager;

    /**
     * AppController constructor.
     * @param CalculatorManager $manager
     */
    public function __construct(CalculatorManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Transmet les données à la vue
     * @param string $result valeur à afficher dans l'élément result-screen
     * @param string $inputs valeur à afficher dans l'élément input-screens
     */
    private function render(string $result, string $inputs)
    {
        require ROOT . '/app/view/calculator.php';
    }

    /**
     * Affiche la calculette
     *
     * - si l'état de la calculette est ACCUMULATE, alors affiche l'accumulateur dans la partie result-screen
     * - sinon affiche le résultat
     */
    public function index()
    {
        $result = $this->manager->isAccumulateState() ?
            $this->manager->getAccumulator() :
            $this->manager->getResult();
        $this->render(
            $result,
            $this->manager->getInput()
        );
    }

    /**
     * Affiche la calculette dans le cas d'une erreur
     * @param string $input le message d'erreur
     */
    public function error(string $input)
    {
        $result = $this->manager->getResult();
        require ROOT . '/app/view/calculator.php';
    }

    /**
     * Ajoute la valeur à l'accumulateur
     * @param string $value valeur numérique
     */
    public function accumulate(string $value)
    {
        try {
            $this->manager->append($value);
            $this->index();
        }
        catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Lance une action
     * @param string $action
     */
    public function action(string $action)
    {
        try {
            switch ($action) {
                case 'clear':
                    $this->manager->clear();
                    break;
                case 'plusmn':
                    $this->manager->swapSign();
                    break;
                case 'divide':
                case 'times':
                case 'minus':
                case 'plus':
                    $this->manager->operation($action);
                    break;
                case 'percnt':
                    $this->manager->percent();
                    break;
                case 'equals':
                    $this->manager->equals();
                    break;
                case 'middot':
                    $this->manager->addComma();
                    break;
                default:
                    throw new Exception('## Erreur : la fonction n\'est pas encore implémentée ##');
            }
            $this->index();
        }
        catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}