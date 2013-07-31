<?php

class Zillow {
	
	const ZWSID = "X1-ZWz1bg4hpm766j_9ej1g";
	protected $xml;
	protected $object;
	protected $result;
	

	public function __construct(){

	}

	public function __set($key, $value){
		$this->$key = $value;
	}

	public function __get($key){
		return $this->key;
	}

	public function getObject(){
		return simplexml_load_string($this->xml);
	}

	public function getXml(){
		return $this->xml;
	}

	public function getQuery(){
		return $this->query;
	}

	private function setXml($result){
		$this->xml = $result;
	}

	private function setQuery($url){
		$this->query = $url;
	}

	public function isError(){
		if($this->object->message->code != 0){
			return true;
		} 

		return false;
	}

	public function getMessageText(){
		return $this->object->message->text;
	}

	private function setProperty($result){
		
		$this->object = simplexml_load_string($result);
		$this->result = $this->object->response->results->result;
		return $this;
	}

	public function searchResults(){
		return $this->request('GetSearchResults');
	}

	public function deepSearchResults(){
		return $this->request('GetDeepSearchResults');
		
	}

	/**
	 * returns the zpid which is used in other calls
	 * @return Int
	 */
	public function getzpid(){
		return $this->object->response->results->result->zpid;
	}

	public function getHomeDetailPageLink(){
		return $this->result->links->homedetails;
	}

	public function getGraphsAndDataLink(){
		return $this->result->links->graphsanddata;
	}

	public function getMapThisHomeLink(){
		return $this->result->links->mapthishome;
	}

	public function getComparablesLink(){
		return $this->result->links->comparables;
	}
	/**
	*
	*/
	public function getAddress(){
		return $this->result->address;
	}

	//public function getNeighborhood(){
	//	return $this->result->localRealEstate->region->attributes()['name'];
	//}

	//public function getNeighborhoodId(){
	//	return $this->result->localRealEstate->region->attributes()['id'];
	//}

	public function getNeighborhoodLinkOverview(){
		return $this->result->localRealEstate->region->links->overview;
	}

	public function getNeighborhoodLinkForSaleByOwner(){
		return $this->result->localRealEstate->region->links->forSaleByOwner;
	}

	public function getNeighborhoodLinkForSale(){
		return $this->result->localRealEstate->region->links->forSale;
	}

	public function getFipsCounty(){
		return $this->result->FIPScounty;
	}

	public function getUseCode(){
		return $this->result->useCode;
	}

	public function getTaxAssessmentYear(){
		return $this->result->taxAssessmentYear;
	}

	public function getTaxAssessment(){
		return $this->result->taxAssessment;
	}

	public function getYearBuilt(){
		return $this->result->yearBuilt;
	}

	public function getLotSizeSqFt(){
		return $this->result->lotSizeSqFt;
	}
	public function getFinishedSqFt(){
		return $this->result->finishedSqFt;
	}

	public function getBathrooms(){
		return $this->result->bathrooms;
	}

	public function getBedrooms(){
		return $this->result->bedrooms;
	}
	public function getTotalRooms(){
		return $this->result->totalRooms;
	}

	public function getLastSoldDate(){
		return $this->result->lastSoldDate;
	}

	public function getLastSoldPrice(){
		return $this->result->lastSoldPrice;
	}

	public function getZestimatePrice(){
		return $this->result->zestimate->amount;
	}
	public function getZestimateLastUpdated(){
		return $this->result->zestimate->{'last-updated'};
	}

	public function getZestimateValueChange(){
		return $this->result->zestimate->valueChange;
	}

	public function getZestimateValuationRangeHigh(){
		return $this->result->zestimate->valuationRange->high;
	}

	public function getZestimateValuationRangeLow(){
		return $this->result->zestimate->valuationRange->low;
	}

	public function getZestimatePercentile(){
		return $this->result->zestimate->percentile;
	}

	/**
	 * [request description]
	 * @param  [type] $type    [description]
	 * @param  [type] $address [description]
	 * @param  [type] $city    [description]
	 * @param  [type] $state   [description]
	 * @param  string $zip     [description]
	 * @return Property        Property Object
	 */
	private function request($type){

		$city_state_zip = $this->city . ", " . $this->state;
		$url = 'http://www.zillow.com/webservice/'.$type.'.htm?zws-id='.self::ZWSID.'&address='.urlencode($this->address).'&citystatezip='.urlencode($city_state_zip);
		
		$this->setQuery($url);

		$ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($ch); 
       
        curl_close($ch);     
        $this->setXml($output);
       
        return $this->setProperty($output);
       
	}

}


class Property extends Zillow{

	public function __construct(){
		
	}

}