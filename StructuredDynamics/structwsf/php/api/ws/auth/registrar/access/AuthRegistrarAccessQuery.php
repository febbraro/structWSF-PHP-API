<?php

  /*! @ingroup StructWSFPHPAPIWebServices structWSF PHP API Web Services */
  //@{

  /*! @file \StructuredDynamics\structwsf\php\api\ws\auth\registrar\access\AuthRegistrarAccessQuery.php  
      @brief AuthRegistrarAccessQuery class description
   */

  namespace StructuredDynamics\structwsf\php\api\ws\auth\registrar\access;

  /**
  * Auth Registrar Access Query to a structWSF Auth Registrar Access web service endpoint
  * 
  * The Auth Registrar: Access Web service is used to register (create, update and delete) 
  * an access for a given IP address, to a specific dataset and all the registered Web 
  * services endpoints registered to the WSF (Web Services Framework) with given CRUD 
  * (Create, Read, Update and Delete) permissions in the WSF. 
  * 
  * This Web service is intended to be used by content management systems, developers or 
  * administrators to manage access to WSF (Web Service Framework) resources (users, 
  * datasets, Web services endpoints).
  * 
  * This web service endpoint is used to create what we refer to as an access permissions 
  * record. This record describe the CRUD permissions, for a certain IP address, to use a 
  * set of web service endpoints, to query a target dataset.
  * 
  * If the IP address that is registered is "0.0.0.0", it refers to the public access of 
  * this dataset. This means that if we define an access permission record for the IP 
  * address 0.0.0.0, to the CRUD permissions "Create: False; Read: True; Update: False; 
  * Delete: False", on the dataset URI http://mydomain.com/wsf/datasets/mydataset/ for 
  * all web service endpoints, this mean that anybody that send a query, to any web 
  * service endpoint, for that dataset, will be granted Read permissions. This means 
  * that this dataset becomes World Readable. 
  * 
  * Here is a code example of how this class can be used by developers: 
  * 
  * @code
  * 
  *  use \StructuredDynamics\structwsf\framework\Namespaces;
  *  use \StructuredDynamics\structwsf\php\api\ws\auth\registrar\access\AuthRegistrarAccessQuery;
  *  use \StructuredDynamics\structwsf\php\api\ws\auth\lister\AuthListerQuery;
  *  use \StructuredDynamics\structwsf\php\api\framework\CRUDPermission;
  *    
  *  // Get all the web services registered on this instance with a 
  *  
  *  // Create the AuthListerQuery object
  *  $authlister = new AuthListerQuery("http://localhost/ws/");
  *  
  *  // Specifies that we want to get all the list of all registered web service endpoints.
  *  $authlister->getRegisteredWebServiceEndpointsUri();
  *  
  *  // Send the auth lister query to the endpoint
  *  $authlister->send();
  *  
  *  // Get back the resultset returned by the endpoint
  *  $resultset = $authlister->getResultset()->getResultset();
  *  
  *  $webservices = array();
  * 
  *  // Get all the URIs from the resultset array
  *  foreach($resultset["unspecified"] as $list)
  *  {
  *    foreach($list[Namespaces::$rdf."li"] as $item)
  *    {
  *      array_push($webservices, $item["uri"]);
  *    }
  *  }
  *  
  *  unset($authlister);
  * 
  *  // Create a new Access record
  *  $ara = new AuthRegistrarAccessQuery("http://localhost/ws/");
  *  
  *  $ara->create("192.168.0.1", "http://localhost/ws/dataset/my-new-dataset-3/", new CRUDPermission(TRUE, TRUE, TRUE, TRUE), $webservices);
  *  
  *  $ara->send();
  * 
  *  if($ara->isSuccessful())
  *  {
  *    // Now, let's make sure that  the record access is properly created.'
  *    // Create the AuthListerQuery object
  *    $authlister = new AuthListerQuery("http://localhost/ws/");
  *    
  *    // Specifies that we want to get all the list of all registered web service endpoints.
  *    $authlister->getDatasetUsersAccesses("http://localhost/ws/dataset/my-new-dataset-3/");
  *    
  *    // Send the auth lister query to the endpoint
  *    $authlister->send();
  *    
  *    // Get back the resultset returned by the endpoint
  *    $resultset = $authlister->getResultset();
  *    
  *    print_r($resultset);      
  *  }  
  *  else
  *  {
  *    echo "Access record creation failed: ".$ara->getStatus()." (".$ara->getStatusMessage().")\n";
  *    echo $ara->getStatusMessageDescription();    
  *  }
  *  
  * @endcode
  * 
  * @see http://techwiki.openstructs.org/index.php/Auth_Registrar:_Access
  * 
  * @author Frederick Giasson, Structured Dynamics LLC.  
  */
  class AuthRegistrarAccessQuery extends \StructuredDynamics\structwsf\php\api\framework\WebServiceQuery
  {
    /**
    * Constructor
    * 
    * @param mixed $network structWSF network where to send this query. Ex: http://localhost/ws/
    */
    function __construct($network)
    {
      // Set the structWSF network to use for this query.
      $this->setNetwork($network);
      
      // Set default configarations for this web service query
      $this->setSupportedMimes(array("text/xml", 
                                     "application/json", 
                                     "application/rdf+xml",
                                     "application/rdf+n3",
                                     "application/iron+json",
                                     "application/iron+csv"));
                                    
      $this->setMethodPost();

      $this->mime("resultset");
      
      $this->setEndpoint("auth/registrar/access/"); 
      
      // Set default parameters for this query
      $this->sourceInterface("default");
    }

    /**
    * Create a new access permissions record
    * 
    * @param mixed $userIP IP address that idenfies the user link to this access record.
    * @param mixed $datasetUri Specifies which dataset URI is targeted by the access record.
    * @param mixed $crudPermission A CRUDPermission object instance that define the permissions granted 
    *                              for the target IP, target Dataset and target Web Service Endpoints of 
    *                              this access permission record.
    * @param mixed $webservicesUris Specifies which web service endpoint URI are targetted by this access record.
    *                               Only the web service endpoints URIs that will be defined in this access record
    *                               will be able to access/use data for the user and dataset defined in this access
    *                               record. Note: you can get the complete list of webservice endpoint URIs 
    *                               registered to a structWSF network instance by using the AuthListerQuery class 
    *                               and by using the getWebServicesList() function.
    * 
    * @see http://techwiki.openstructs.org/index.php/Auth_Registrar:_Access#Web_Service_Endpoint_Information
    * 
    * @author Frederick Giasson, Structured Dynamics LLC.* 
    */
    public function create($userIP, $datasetUri, $crudPermission, $webservicesUris)
    {
      $this->params["registered_ip"] = urlencode($userIP);                                                                                   
      
      $this->params["crud"] = urlencode(($crudPermission->getCreate() ? "True" : "False").";".
                                        ($crudPermission->getRead() ? "True" : "False").";".
                                        ($crudPermission->getUpdate() ? "True" : "False").";".
                                        ($crudPermission->getDelete() ? "True" : "False"));      
                                        
      $this->params["ws_uris"] = urlencode(implode(";", $webservicesUris));   
      
      $this->params["dataset"] = urlencode($datasetUri);    
      
      $this->params["action"] = "create";
    }
      
    /**
    * Delete a target access permissions record for a specific IP address and a specific dataset 
    * 
    * @param mixed $userIP User IP address defined for the access record to delete
    * @param mixed $datasetUri Dataset URI defined for the access record to delete
    * 
    * @see http://techwiki.openstructs.org/index.php/Auth_Registrar:_Access#Web_Service_Endpoint_Information
    * 
    * @author Frederick Giasson, Structured Dynamics LLC.* 
    */
    public function deleteTarget($userIP, $datasetUri)
    {
      $this->params["registered_ip"] = urlencode($userIP);   
      
      $this->params["dataset"] = urlencode($datasetUri); 
      
      $this->params["action"] = "delete_target";
    }
    
      
    /**
    * Delete a specific access record
    * 
    * @param mixed $accessRecordUri URI of the access record to deleten from the system
    * 
    * @see http://techwiki.openstructs.org/index.php/Auth_Registrar:_Access#Web_Service_Endpoint_Information
    * 
    * @author Frederick Giasson, Structured Dynamics LLC.* 
    */
    public function deleteSpecific($accessRecordUri)
    {
      $this->params["target_access_uri"] = urlencode($accessRecordUri);   
      
      $this->params["action"] = "delete_specific";
    }    

    /**
    * Delete a target access permissions record for a specific IP address and a specific dataset 
    *     
    * @param mixed $datasetUri Dataset URI for which we delete all the access record defined for it
    * 
    * @see http://techwiki.openstructs.org/index.php/Auth_Registrar:_Access#Web_Service_Endpoint_Information
    * 
    * @author Frederick Giasson, Structured Dynamics LLC.* 
    */
    public function deleteAll($datasetUri)
    {
      $this->params["dataset"] = urlencode($datasetUri);
      
      $this->params["action"] = "delete_all";
    }
    
   /**
    * Create a new access permissions record
    * 
    * @param mixed $accessRecordUri Access record URI to modify
    * @param mixed $userIP IP address that idenfies the user link to this access record.
    * @param mixed $datasetUri Specifies which dataset URI is targeted by the access record.
    * @param mixed $crudPermission A CRUDPermission object instance that define the permissions granted 
    *                              for the target IP, target Dataset and target Web Service Endpoints of 
    *                              this access permission record.
    * @param mixed $webservicesUris Specifies which web service endpoint URI are targetted by this access record.
    *                               Only the web service endpoints URIs that will be defined in this access record
    *                               will be able to access/use data for the user and dataset defined in this access
    *                               record. Note: you can get the complete list of webservice endpoint URIs 
    *                               registered to a structWSF network instance by using the AuthListerQuery class 
    *                               and by using the getWebServicesList() function.
    * 
    * @see http://techwiki.openstructs.org/index.php/Auth_Registrar:_Access#Web_Service_Endpoint_Information
    * 
    * @author Frederick Giasson, Structured Dynamics LLC.* 
    */
    public function update($accessRecordUri, $userIP, $datasetUri, $crudPermission, $webservicesUris)
    {
      $this->params["target_access_uri"] = urlencode($accessRecordUri);                                                                             
      
      $this->params["registered_ip"] = urlencode($userIP);                                                                                         
      
      $this->params["crud"] = urlencode(($crudPermission->getCreate() ? "True" : "False").";".
                                        ($crudPermission->getRead() ? "True" : "False").";".
                                        ($crudPermission->getUpdate() ? "True" : "False").";".
                                        ($crudPermission->getDelete() ? "True" : "False"));      
                                        
      $this->params["ws_uris"] = urlencode(implode(";", $webservicesUris));   
      
      $this->params["dataset"] = urlencode($datasetUri); 
      
      $this->params["action"] = "update";
    }     
   }       
 
//@}    
?>