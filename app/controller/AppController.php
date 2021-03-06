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
     */
    public function index()
    {
        $this->render('Afin qu\'elle affiche le résultat désiré', 'Modifier la méthode index');
    }

    /**
     * Affiche la calculette dans le cas d'une erreur
     * @param string $input le message d'erreur
     */
    public function error(string $input)
    {
    }

    /**
     * Ajoute la valeur à l'accumulateur
     * @param string $value valeur numérique
     */
    public function accumulate(string $value)
    {
        // TODO: implémenter la méthode
        $this->index();
    }

    /**
     * Lance une action (opération, égal, pourcentage, ...)
     * @param string $action
     */
    public function action(string $action)
    {
        // TODO: implémenter la méthode
        $this->index();
    }
}