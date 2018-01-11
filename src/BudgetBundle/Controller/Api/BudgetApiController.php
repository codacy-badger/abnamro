<?php

namespace BudgetBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

// For forms
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

// Entity Budget
use BudgetBundle\Entity\Budget;
use BudgetBundle\Form\BudgetType;

class BudgetApiController extends Controller
{
    /**
     *
     * @Route("/api/budget", name="get_budget")
     */
    public function getAction()
    {

    }
}
