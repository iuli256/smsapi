<?php
/**
 * Created by PhpStorm.
 * User: iuli
 * Date: 1/7/2019
 * Time: 9:34 PM
 */
namespace AppBundle\Service;
use Symfony\Component\Config\Definition\Exception\Exception;

class InputValidator
{
    public function validateInputMethod($request){
        if ($request->isMethod('POST')) {
            return $this->validateInputHeader($request);
        }else {
            throw new Exception('all request should be made only using POST');
        }
    }

    private function validateInputHeader($request){
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            return $this->validateInputContentExist($request);
        }else{
            throw new Exception('Content-Type should be application/json');
        }
    }

    private function validateInputContentExist($request){
        if ($request->getContent()) {
            return $this->validateInputIsJson($request);
        }else{
            throw new Exception('no data received on POST');
        }
    }

    private function validateInputIsJson($request){
        try{
            $content = $request->getContent();
            $data = json_decode($content, true);
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    return $this->validateInputIsArray($data);
                    break;
                case JSON_ERROR_DEPTH:
                    throw new Exception('Input json - Maximum stack depth exceeded');
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    throw new Exception('Input json - Underflow or the modes mismatch');
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    throw new Exception('Input json - Unexpected control character found');
                    break;
                case JSON_ERROR_SYNTAX:
                    throw new Exception('Input json - Syntax error, malformed JSON');
                    break;
                case JSON_ERROR_UTF8:
                    throw new Exception('Input json - Malformed UTF-8 characters, possibly incorrectly encoded');
                    break;
                default:
                    throw new Exception('Input json - Unknown error');
                    break;
            }
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    private function validateInputIsArray($data){
        if (is_array($data)){
            return $this->validateInputRecipient($data);
        }else{
            throw new Exception('Input json - is not an array');
        }

    }

    private function validateInputRecipient($data){
        if (isset($data['recipient'])){
            return $this->validateRecipientFormat($data);
        }else{
            throw new Exception('recipient is not defined');
        }

    }

    private function validateRecipientFormat($data){
        if (preg_match('/^[1-9][0-9]{9,14}$/', $data['recipient'])) {
            return $this->validateInputOriginator($data);
        }else{
            throw new Exception('recipient have not the correct format. it should be an international phone number without leading 0. ex: 40723123789');
        }

    }

    private function validateInputOriginator($data){
        if (isset($data['originator'])){
            return $this->validateOriginatorFormat($data);
        }else{
            throw new Exception('originator is not defined');
        }

    }

    private function validateOriginatorFormat($data){
        if ($data['originator'] == 'MessageBird') {
            return $this->validateInputMessage($data);
        }else{
            throw new Exception('originator can be only MessageBird');
        }

    }

    private function validateInputMessage($data){

        if (isset($data['message'])) {
            return $this->validateMessageFormat($data);
        }else{
            throw new Exception('message is not defined');
        }

    }

    private function validateMessageFormat($data){
        if (preg_match('/^[a-zA-Z0-9~!@#$%^&*()`\[\]{};\':,.\/<>?| ]{1,159}$/', $data['message'])){
            return  $data;
        }else{
            throw new Exception('message must not exced 160 characters');
        }

    }
}