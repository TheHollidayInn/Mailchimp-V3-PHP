<?php
class Mailchimp
{
  private $dc;
	private $key;
	private $urlProtocol = 'https://';
	private $apiEndPoint = '.api.mailchimp.com/3.0';

	private $url;
	private $data;

  public function __construct($key, $dc)
  {
    $this->key = $key;
    $this->dc = $dc;
  }

	private function mcCurl($post = "GET") {

		$data = json_encode($this->data);

    $method = "GET";

    if ($post == "PUT") {
      $method = "PUT";
    } else if ($post == "PATCH") {
      $method = "PATCH";
    } else if ($post == 1 || $post == "POST") {
      $method = "POST";
    } else {
      if (isset($this->data)) {
        $getData = http_build_query($this->data);
        $this->url .= "?$getData";
      }
    }

    $ch = curl_init($this->url);
    curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $this->key);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $result = curl_exec($ch);

	  return json_decode($result);
	}

  //Lists
  public function createList($name)
  {
    $data = array(
      "name" => $name,
      "contact" => array(
        "company" => "ReadyRosie",
        "address1" => "121 W Hickory Suite 202",
        "city" => "Denton",
        "state" => "TX",
        "zip" => "76201",
        "country" => "US",
      ),
      "permission_reminder" => "You have signed up to receive educational activities from $name.",
      "campaign_defaults" => array(
        "from_name" => "ReadyRosie",
        "from_email" => "info@readyrosie.com",
        "subject" => "ReadyRosie",
        "language" => "english",
      ),
      "email_type_option" => false,
    );

    $this->data = $data;
    $this->url = $this->urlPrefix.$this->apiLink.'/lists';

    return $this->mcCurl("POST");
  }

	public function getLists($offset = 0, $limit = 25)
  {
		$data = array(
			"offset" => $offset,
			"count" => $limit,
		);

		$this->data = $data;
		$this->url = $this->urlPrefix.$this->apiLink.'/lists';

		return $this->mcCurl();
	}

  public function getListActivty($listId, $count = 10)
  {
    $data = array(
			"count" => $count,
		);

		$this->data = $data;
		$this->url = $this->urlPrefix.$this->apiLink."/lists/$listId/activity";

		return $this->mcCurl();
	}

  public function getListGrowthHistory($listId, $limit = 100)
  {
    $data = array(
			"count" => $limit,
		);

		$this->data = $data;

		$this->url = $this->urlPrefix.$this->apiLink."/lists/$listId/growth-history";

		return $this->mcCurl();
	}

  public function getListClients($listId)
  {
    $data = array();
		$this->data = $data;

		$this->url = $this->urlPrefix.$this->apiLink."/lists/$listId/clients";

		return $this->mcCurl("GET");
	}

  //Campaigns
  public function getCampaignFolders()
  {
    $this->data = array();
    $this->url = $this->urlPrefix.$this->apiLink.'/campaign-folders';

    return $this->mcCurl();
  }

	public function getCampaignLists($list_id, $limit = 25, $startDate = "2015-06-01 00:00:00", $endDate = "2015-10-31 00:00:00")
  {

    $startDate = new DateTime($startDate);
    $startDate->format(DateTime::ISO8601);

    $endDate = new DateTime($endDate);
    $endDate->format(DateTime::ISO8601);

    $data = array(
      "list_id" => $list_id,// @TODO: How do we filter by list?
			"since_create_time" => $startDate->format(DateTime::ISO8601),
			"before_create_time" => $endDate->format(DateTime::ISO8601),
			"count" => $limit,
		);

		$this->data = $data;
		$this->url = $this->urlPrefix.$this->apiLink.'/campaigns';

		return $this->mcCurl();
	}

  public function createTemplate($name, $html)
  {
    $data = array(
      "name" => $name,
			"html" => $html,
      // "folder_id" => "4801",
		);

		$this->data = $data;
		$this->url = $this->urlPrefix.$this->apiLink.'/templates';

		return $this->mcCurl(1);
	}

  public function createCampaign($campaign, $email, $text)
  {
    $campaign["type"] = "regular";

		$this->data = $campaign;
		$this->url = $this->urlPrefix.$this->apiLink.'/campaigns';

		return $this->mcCurl(1);
	}

  public function putCampaignContent($campaignId, $html, $text)
  {
    $data = array(
			"html" => $html,
      "plain_text" => $text,
		);

		$this->data = $data;
		$this->url = $this->urlPrefix.$this->apiLink."/campaigns/$campaignId/content";

		return $this->mcCurl("PUT");
	}

  public function sendCampaign($campaign)
  {
    $this->data = array();
		$this->url = $this->urlPrefix.$this->apiLink."/campaigns/$campaign->id/actions/send";

		return $this->mcCurl("POST");
	}

	public function getMemberActivity($list_id, $emails = array())
  {
		$data = array(
			// "emails" => $emails,
		);

		$this->data = $data;
		$this->url = $this->urlPrefix.$this->apiLink. "/lists/$list_id/activity";

		return $this->mcCurl("GET");
	}

  public function getListMembers($list_id, $count = 10)
  {
    $data = array(
      'count' => $count,
    );

    $this->data = $data;
		$this->url = $this->urlPrefix.$this->apiLink. "/lists/$list_id/members";

		return $this->mcCurl("GET");
  }

  public function getListMemberActivity($list_id, $subscriber_hash)
  {
    $this->url = $this->urlPrefix.$this->apiLink. "/lists/$list_id/members/$subscriber_hash/activity";

		return $this->mcCurl("GET");
  }

  public function getBatches()
  {
		$this->url = $this->urlPrefix.$this->apiLink.'/batches';

		return $this->mcCurl();
  }

	public function batchSubscribe($list_id, $emails)
  {
    $date = new DateTime();
    $data = array(
      "operations" => array()
    );
    foreach ($emails as $email) {
      $operation = array(
        "method" => "POST",
        "path" => "lists/$list_id/members",
        "operation_id" => "$list_id" . $date->getTimestamp() . '-' . $email['email_address'],
        "body" => json_encode($email),
      );
      array_push($data['operations'], $operation);
    }

		$this->data = $data;
		$this->url = $this->urlPrefix.$this->apiLink.'/batches';

		return $this->mcCurl(1);
	}

  public function batchUnSubscribe($list_id, $emails, $delete_member = false)
  {

    $data = array(
      "apikey" => $this->key,
      "id" => $list_id,
      "batch" => $emails,
      "delete_member" => $delete_member,
      "send_goodbye" => "false",
    );
		$this->data = $data;
		$this->url = $this->urlPrefix.$this->apiLink.'/lists/batch-unsubscribe';

		return $this->mcCurl(1);
	}

	//Reports
	public function getUnsubscribeReport($campaign_id)
  {
		$data = array(
			"apikey" => $this->key,
			"cid" => $campaign_id,
			//"ops" => $emails,
		);

		$this->data = $data;
		$this->url = $this->urlPrefix.$this->apiLink.'/reports/unsubscribes';

		return $this->mcCurl();
	}

	public function getCampaignReport($campaign_id)
  {
		$this->url = $this->urlPrefix.$this->apiLink."/reports/$campaign_id";

		return $this->mcCurl();
	}

  //Export API
  public function getUserExportForList($listID, $status = "subscribed")
  {
  	//Get MC users for rr_mailchimp_settings
  	$mc_api_url = 'http://us5.api.mailchimp.com/export/1.0/list/?apikey=264fd6ce3af9715e32d9f0ac0d421e58-us5&id='.$listID;
    $mc_api_url .= "&status=$status";
   	$ch = curl_init();

  	curl_setopt($ch, CURLOPT_HEADER, 0);
  	curl_setopt($ch, CURLOPT_HTTPGET, 1);
  	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  	curl_setopt($ch, CURLOPT_URL, $mc_api_url);

  	$data = curl_exec($ch);
  	curl_close($ch);

  	$retrievedData = preg_split('/\r\n|\r|\n/', $data);
  	return $retrievedData;
  }

  public function getUserActivityExportForCampaign($campaignId)
  {
  	//Get MC users for rr_mailchimp_settings
  	$mc_api_url = 'http://us5.api.mailchimp.com/export/1.0/campaignSubscriberActivity/';
    $mc_api_url .= '?apikey=264fd6ce3af9715e32d9f0ac0d421e58-us5&id='.$campaignId;

  	$ch = curl_init();

  	curl_setopt($ch, CURLOPT_HEADER, 0);
  	curl_setopt($ch, CURLOPT_HTTPGET, 1);
  	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  	curl_setopt($ch, CURLOPT_URL, $mc_api_url);

  	$data = curl_exec($ch);
  	curl_close($ch);

  	$retrievedData = preg_split('/\r\n|\r|\n/', $data);
  	return $retrievedData;
  }

  public function subscribe($listId, $email)
  {
    $data = array(
      "status" => "subscribed",
      "email_address" => $email,
    );

		$this->data = $data;
		$this->url = $this->urlPrefix.$this->apiLink."/lists/$listId/members";

		return $this->mcCurl(1);
	}

}


 ?>
