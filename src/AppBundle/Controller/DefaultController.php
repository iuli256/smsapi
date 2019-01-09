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
    protected $response = array('status' => '', 'data' => '', 'message' => '');

    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @param MessageService $messageService
     * @return JsonResponse
     */
    public function indexAction(Request $request, MessageService $messageService)
    {
        try{
            $response = '';
            $inputValidator = new InputValidator();
            $Input = $inputValidator->validateInputMethod($request);
            if($Input) {
                $response = $messageService->process($Input);
            }
            return $this->sendResponse($response);
        }
        catch(\Exception  $e){
            $this->response['status'] = 'error';
            $this->response['message'] = $e->getMessage();
            return $this->sendResponse($this->response);
        }
    }



    private function sendResponse($response){

        return new JsonResponse($response);
    }
}
