<?php


namespace App\Model;

use App\Helper\NumericHelper;
use Exception;

/**
 * Classe CalculatorManager, pilote la calculatrice.
 * La classe doit disposer de différentes méthodes permettant d'effectuer les opérations
 * @package App\Model
 */
class CalculatorManager
{
    const INPUT_CONTROLS = [
        'divide' => Calculator::DIVIDE,
        'times' => Calculator::TIMES,
        'minus' => Calculator::MINUS,
        'plus' => Calculator::PLUS
    ];

    private $calc;

    /**
     * CalculatorManager constructor.
     * @param $calc
     */
    public function __construct(Calculator $calc)
    {
        $this->calc = $calc;
    }

    /**
     * Réinitialise la calculette
     */
    public function clear()
    {
        try {
            $this->calc->setResult(0);
            $this->calc->setInput('');
            $this->calc->setAccumulator(Calculator::INIT_VALUE);
            $this->calc->setOperator(Calculator::OPERATOR_INIT_VALUE);
            $this->calc->setState(Calculator::ACCUMULATE_STATE);
        } catch (Exception $e) {
            // Aucune exception ne sera levée
        } finally {
            session_destroy();
        }
    }

    /**
     * Effectue un calcul de la calculette et retourne le résultat
     * @return string résultat du calcul
     * @throws Exception
     */
    private function calculate(): string
    {
        $value = $this->calc->getAccumulator();

        if (!is_numeric($value)) {
            throw new Exception(
                '## Erreur : cette opération (& ' . $value . ';) nécessite une valeur numérique ##'
            );
        }

        $this->calc->setState(Calculator::ACCUMULATE_STATE);

        switch ($this->calc->getOperator()) {
            case 'divide':
                if ($this->calc->getInput() !== ''
                    && $this->isAccumulateState()
                    && NumericHelper::isZero((float)$value)) {
                    throw new Exception('## Erreur : la division par 0 n\'est pas autorisée ##');
                }
                $this->divide($value);
                break;
            case 'times':
                $this->times($value);
                break;
            case 'minus':
                $this->minus($value);
                break;
            case 'plus':
                $this->plus($value);
                break;
            default:
                throw new Exception('## Erreur : opération non reconnue ##');
        }
        return $this->calc->getResult();
    }

    /**
     * Effectue l'opération equals. Effectue le calcul de la calculette.
     * @throws Exception
     */
    public function equals()
    {
        if (!$this->isResultState()) {
            $this->calc->setInput($this->calc->getInput() . $this->calc->getAccumulator());
            $this->calc->setResult(
                $this->calc->getOperator() !== Calculator::OPERATOR_INIT_VALUE ?
                    $this->calculate() :
                    $this->calc->getAccumulator()
            );
            $this->calc->setAccumulator(Calculator::INIT_VALUE);
            $this->calc->setState(Calculator::RESULT_STATE);
            $this->calc->setOperator(Calculator::OPERATOR_INIT_VALUE);
        }
    }

    /**
     * Inverse le signe de l'accumulateur si l'état est ACCUMULATE, sinon inverse le signe du résultat et met à jour
     * les inputs
     */
    public function swapSign()
    {
        try {
            if ($this->calc->getState() === Calculator::ACCUMULATE_STATE) {
                $accu = $this->calc->getAccumulator();
                if ($accu != Calculator::INIT_VALUE) {
                    $this->calc->setAccumulator(strpos($accu, Calculator::MINUS) === 0 ?
                        substr($accu, 1) :
                        Calculator::MINUS . $accu
                    );
                }
            }
            else {
                $this->calc->setResult(- $this->calc->getResult());
                $this->calc->setInput('-(' . $this->calc->getInput() . ')');
            }
        } catch (Exception $ignored) {
            // Aucune exception ne peut être levée ici.
        }
    }

    /**
     * Additionne la valeur en paramètres au résultat de la calculette
     * @param string $value la valeur à additionner au résultat
     * @throws Exception
     */
    public function plus(string $value)
    {
        if (NumericHelper::isFloat($this->calc->getResult()) || NumericHelper::isFloat($value)) {
            $this->calc->setResult((float)$this->calc->getResult() + (float)$value);
        } else {
            $this->calc->setResult(((int)$this->calc->getResult()) + ((int)$value));
        }

        $this->calc->setAccumulator(Calculator::INIT_VALUE);
        $this->calc->setOperator(Calculator::OPERATOR_INIT_VALUE);
    }

    /**
     * @param $value
     * @throws Exception
     */
    public function divide($value)
    {
        if (NumericHelper::isZero((float)$value)) {
            $input = $this->calc->getInput();
            $this->calc->setInput(substr($input, 0, strlen($input) - strlen($value) - 1));
            throw new Exception('## Erreur : la division par 0 n\'est pas autorisée ##');
        }
        $isMultiple = (int)$this->calc->getResult() % ((int)$value) === 0;
        if (NumericHelper::isFloat($this->calc->getResult())
            || NumericHelper::isFloat($value)
            || !$isMultiple
        ) {
            $this->calc->setResult((float)$this->calc->getResult() / (float)$value);
        } else {
            $this->calc->setResult(((int)$this->calc->getResult()) / ((int)$value));
        }

        $this->calc->setAccumulator(Calculator::INIT_VALUE);
        $this->calc->setOperator(Calculator::OPERATOR_INIT_VALUE);
    }

    /**
     * @param $value
     * @throws Exception
     */
    public function times($value)
    {
        if (NumericHelper::isFloat($this->calc->getResult()) || NumericHelper::isFloat($value)) {
            $this->calc->setResult((float)$this->calc->getResult() * (float)$value);
        } else {
            $this->calc->setResult(((int)$this->calc->getResult()) * ((int)$value));
        }

        $this->calc->setAccumulator(Calculator::INIT_VALUE);
        $this->calc->setOperator(Calculator::OPERATOR_INIT_VALUE);
    }

    /**
     * @param $value
     * @throws Exception
     */
    public function minus($value)
    {
        if (NumericHelper::isFloat($this->calc->getResult()) || NumericHelper::isFloat($value)) {
            $this->calc->setResult((float)$this->calc->getResult() - (float)$value);
        } else {
            $this->calc->setResult(((int)$this->calc->getResult()) - ((int)$value));
        }

        $this->calc->setAccumulator(Calculator::INIT_VALUE);
        $this->calc->setOperator(Calculator::OPERATOR_INIT_VALUE);
    }

    /**
     * @param string $action
     * @throws Exception
     */
    public function operation(string $action)
    {
        if ($action === 'divide'
            && $this->isAccumulateState()
            && $this->calc->getInput() !== ''
            && NumericHelper::isZero((float)$this->calc->getAccumulator())
        ) {
            throw new Exception('## Erreur : la division par 0 n\'est pas autorisée ##');
        }

        if ($action !== null && !array_key_exists($action, self::INPUT_CONTROLS)) {
            throw new Exception('## Erreur : opérateur non valide ##');
        }

        // ACCUMULATION_STATE
        if ($this->isAccumulateState()) {
            $input = $this->getInput() .
                $this->getAccumulator() . self::INPUT_CONTROLS[$action];
            $result = $this->calc->getOperator() !== Calculator::OPERATOR_INIT_VALUE ?
                $this->calculate() :
                $this->getAccumulator();
            $this->calc->setResult($result);
        }
        // RESULT_STATE
        else {
            $this->calc->setOperator($action);
            $input = $this->calc->getInput() . self::INPUT_CONTROLS[$action];
            $this->calc->setState(Calculator::ACCUMULATE_STATE);
        }
        $this->calc->setInput($input);
        $this->calc->setOperator($action);
        $this->calc->setAccumulator(Calculator::INIT_VALUE);
    }

    /**
     * Ajoute une virgule à l'accumulateur si c'est possible.
     * @throws Exception si l'accumulateur est déjà un nombre à virgule
     */
    public function addComma()
    {
        if (NumericHelper::isFloat($this->calc->getAccumulator())) {
            throw new Exception('## Erreur : l\'accumulateur correspond déjà à un nombre décimal ##');
        }

        $this->calc->setAccumulator($this->calc->getAccumulator() . '.');
    }

    /**
     *
     */
    public function percent()
    {
        try {
            $result = $this->calc->getState() == Calculator::RESULT_STATE ?
                $this->calc->getResult() :
                $this->calc->getAccumulator();
            $input = $this->calc->getInput();
            $this->calc->setInput(
                strlen($input) > 0 ? $input . Calculator::PERCENT : $result . Calculator::PERCENT
            );
            if (NumericHelper::isFloat($result)
                || strlen($result) < 2
            ) {
                $result = (float)$result / 100.0;
            } else {
                $result = $result / 100;
            }
            $this->calc->setResult($result);
            $this->calc->setAccumulator(Calculator::INIT_VALUE);
            $this->calc->setState(Calculator::RESULT_STATE);
        } catch (Exception $ignored) {
            // Aucune exception ne sera lancée
        }
    }

    /**
     * @param string $value
     * @throws Exception
     */
    public function append(string $value)
    {
        $accu = $this->calc->getAccumulator();

        if ($accu === Calculator::INIT_VALUE) {
            $accu = $value;
        }
        else {
            $accu = $accu . $value;
        }

        $this->calc->setAccumulator($accu);
    }

    /**
     * @return bool
     */
    public function isAccumulateState(): bool
    {
        return $this->calc->getState() == strval(Calculator::ACCUMULATE_STATE);
    }

    /**
     * @return bool
     */
    public function isResultState(): bool
    {
        return $this->calc->getState() === strval(Calculator::RESULT_STATE);
    }

    /**
     * @return string
     */
    public function getResult(): string
    {
        return $this->calc->getResult();
    }

    /**
     * @return string
     */
    public function getInput(): string
    {
        return $this->calc->getInput();
    }

    /**
     * @return string
     */
    public function getAccumulator(): string
    {
        return $this->calc->getAccumulator();
    }
}