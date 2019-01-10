<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Service\InputValidator;
use AppBundle\Service\MessageService;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @param MessageService $messageService
     *  @var MessageService $messageService
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        try{
            $response = '';
            $inputValidator = new InputValidator();
            $Input = $inputValidator->validateInputMethod($request);
            if($Input) {
                $response = $messageService->process($Input);
            }
        }
        catch(\Exception  $e){
            $response['status'] = 'error';
            $response['data'] = '';
            $response['message'] = $e->getMessage();
        }
        return new JsonResponse($response);
    }


}
