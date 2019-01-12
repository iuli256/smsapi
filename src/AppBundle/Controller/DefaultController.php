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
      * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        $response = array();
        try{
            /*  @var MessageService $messageService */
            $messageService = $this->get('message.custom');
            $inputValidator = new InputValidator();
            $Input = $inputValidator->validateInputMethod($request);
            if($Input) {
                $smskey = $this->getParameter('smskey');
                $response = $messageService->process($Input, $smskey);
            }
        }
        catch(\Exception  $e){
            $response['status'] = 'error';
            $response['data'] = '';
            $response['message'] = $e->getMessage();
        }
        return new JsonResponse($response);
    }

    /**
     * @Route("/cron/", name="cron")
     * @param Request $request
     * @return JsonResponse
     */
    public function cronAction(Request $request){
        $response = array();
        try{
            /*  @var MessageService $messageService */
            $messageService = $this->get('message.custom');
            $smskey = $this->getParameter('smskey');
            $response = $messageService->cron($smskey);

        }catch (\Exception $e){
            $response['status'] = 'error';
            $response['data'] = '';
            $response['message'] = $e->getMessage();
        }
        return new JsonResponse($response);
    }
}
