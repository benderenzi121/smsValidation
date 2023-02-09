<?php
    namespace Drupal\sms_validation\Service;

    use Drupal;
    use Symfony\Component\HttpFoundation\Response;
    use Twilio\Exceptions\TwilioException;
    use Twilio\Rest\Client;
    use Drupal\Core\Site\Settings;
    use Symfony\Component\HttpFoundation\JsonResponse;

    class TwilioAPIConnector{
        
    /**
     * Twilio  client variable;.
     *
     * @var /Twilio\Rest\Client
     */    
    protected $twilioClient;

     /**
     * Constructor function that creats an instance of our twilio client
     *  based on the SID and AuthToken in our settings.php file
     * 
     */    

    public function __construct(\Drupal\core\http\ClientFactory $client)
    {
        $sid = 'AC91121581a65a14958ee7521f8576a3a5';
        $token = '9938523d31aa68e7d4d76ee5126a24a6';

        $this->twilioClient = new Client($sid, $token);

    }

    /**
     * Our Verify Number function hits the twilio API to lookup a phone number
     *  
     * As long as the request from twilio comes back, it will return the data provided by twillio. 
     * @param string $phone_number
     * 
     */

    public function verifyNumber($phone_number)
    {
        // Initialize an empty array that our data will eventually go into
        $data = [];

        /**
        *   Try to hit the twilio endpoint with the phone number provided
        *       will populate our data array if there is a response from the twilio api
        *       
        *       If there is no response we return an array with nothing inside of it 
        *           and throow an exception
        */
        try {
            $validation = $this->twilioClient->lookups->v2->phoneNumbers($phone_number)->fetch();
            $data = $validation;
        }
        catch (TwilioException $e) {
            throw $e;
        }
        catch(\GuzzleHttp\Exception\RequestException $e){
            watchdog_exception('twilio', $e, $e->getMessage());
        }

        return $data;
    }

     /**
      *  VerifySms function is used to verify that the number provided is a valid mobile number
      *      from within the US. 
      * 
      * @param string $phone_number
      *
      */
    public function verifySms($phone_number)
    {
        try {
            $validation = $this->twilioClient->lookups->v1->phoneNumbers($phone_number)->fetch(["type" => ["carrier"]]);
        }
        catch (TwilioException $e) {
            throw $e;
        }
        catch(\GuzzleHttp\Exception\RequestException $e){
            watchdog_exception('twilio', $e, $e->getMessage());
        }
        return $validation->carrier['type'];
    }

}
?> 
