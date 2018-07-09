<?php

namespace aws_client;

require_once($GLOBALS["sb_code_path"] . "/shared_code/aws_client/vendor/autoload.php");
require_once($GLOBALS["sb_code_path"] . "/shared_code/machine_configuration/machine_configuration.php");
#require_once("aws")
#require_once 'AWSSDKforPHP/aws.phar';
#require_once('AWSSDKforPHP/aws.phar');

use Aws\Common\Enum;
use Aws\Sdk;
//use Aws\Credentials\CredentialProvider;
use Aws\Credentials\Credentials;
//use Aws\Credentials;

class aws_cloudfront_client {


    
    public $machine_config_base_path = "/etc/";
    
    public function get_hosted_zone_id($aws_region)
    {
        $hosted_zone_id = "";
        if ($aws_region == "usw-2")
        {
            $hosted_zone_id = "Z16Q9R4R9FRFA0";
        }  elseif ($aws_region == "ap-2")
        {
                #private $hosted_zone_id_au = "Z1TJKC7UH0W3H4";
                     $hosted_zone_id = "Z16Q9R4R9FRFA0";
        }         elseif ($aws_region == "dev-1") {
            #$hosted_zone_id = "ZIDLVW73G3PZI"; #dev
            $hosted_zone_id = "Z2NP2D71D83Q8E";
        } elseif ($aws_region == "test-1")
        {
            #$hosted_zone_id = "Z1C82NPWMMAACS"; #test
            $hosted_zone_id = "Z3PZKE918R9AR1";
        }
    
    return $hosted_zone_id;
    }

    public function __construct() {
        
    }

    public function get_cloudfront_name($aws_region, $server_hostname) {
        $cdn = "";
        # CDN ID is no longer required as it can be used to delete CDNs
        $cdn_id = "";
        if ($aws_region == "ie") {
            $cdn = "d165q429q1382t.cloudfront.net";
        } else if ($aws_region == "ap-2") {
            $cdn = "d2sa71md407dd.cloudfront.net";
        } else if ($aws_region == "sg") {
            $cdn = "d165q429q1382t.cloudfront.net";
        } else if ($aws_region == "jp") {
            $cdn = "d165q429q1382t.cloudfront.net";
        } else if ($aws_region == "br") {
            $cdn = "d165q429q1382t.cloudfront.net";
        } else if ($aws_region == "use") {
            $cdn = "d165q429q1382t.cloudfront.net";
        } else if ($aws_region == "usw-2") {
            $cdn = "d3a1vhhiw9y8xm.cloudfront.net";
        } else if ($aws_region == "dev-1") {
            $cdn = "d234qs1h62sqlf.cloudfront.net";
        } else if ($aws_region == "test") {
            $cdn = "d165q429q1382t.cloudfront.net";
        }

        if ($server_hostname == "au1") {
            $cdn = "dlie7z26dw5rw.cloudfront.net";
        } elseif ($server_hostname == "au2") {
            $cdn = "d3dys9e8mmi9w4.cloudfront.net";
        } elseif ($server_hostname == "au3") {
            $cdn = "d3n85ztdlmvefo.cloudfront.net";
        } elseif ($server_hostname == "ie1") {
            $cdn = "d165q429q1382t.cloudfront.net";
        } elseif ($server_hostname == "ie2") {
            $cdn = "d4oygttordptr.cloudfront.net";
        } elseif ($server_hostname == "ie3") {
            $cdn = "d2oej04zg7a4g9.cloudfront.net";
        }

        return array($cdn, $cdn_id);
    }



    /*
     * Performance of uploading a single file with 12 single image file uploads
     * 35 seconds for our 12 files usign this method - putObject & upload
     * reuploading the same files takes the same time (there's no dupe check)
     */
    
    public function get_machine_config_path_ext($prefix, $region, $extension) {
        /*
        # Open the memcache config and get the server list for this server region
        $config_filename = "/etc/shopsbee/" . $prefix . "_" . $region . ".txt";
        //$config_filename = $GLOBALS["sb_code_path"] . '/machine_config' . "/" . $prefix . "_" . $region . ".txt";

        $config = new \shared_code\configuration($config_filename);

        return $config;
         * 
         */
        
        $machine_configuration_class = new \shared_code\machine_configuration();
        
        $config = $machine_configuration_class -> get_machine_config_path_ext($prefix, $region, "php");
        
        return $config;
    } 

    public function get_credentials($region)
    {
        $machine_configuration_class = new \shared_code\machine_configuration();
        
        $prefix = "aws_new";
        
        //$config_path = $machine_configuration_class -> get_machine_config_path_ext($prefix, $region, "php");
        
        $config = $machine_configuration_class -> get_machine_config($prefix, $region);
        
        $key = $config -> get_setting("key");
        $secret = $config -> get_setting("secret");
        
        //$credentials = new Aws\Credentials\Credentials($key, $secret);
        $credentials = new Credentials($key, $secret);
        
        return $credentials;
    }
    
    public function get_real_aws_region($region)
    {
        $machine_configuration_class = new \shared_code\machine_configuration();
        
        $prefix = "aws_new";
        
        //$config_path = $machine_configuration_class -> get_machine_config_path_ext($prefix, $region, "php");
        
        $config = $machine_configuration_class -> get_machine_config($prefix, $region);
        
        $real_region = $config -> get_setting("region");
        
        
        return $real_region;
    }


    public function delete_cloudfront($aws_region, $site_id, $distributionId, $etag) {
        $deployed = false;
        $error_messages = array();
        $credentials = $this -> get_credentials($aws_region);
        
        $region = $this -> get_real_aws_region($aws_region);

        $cloudfront = new \Aws\CloudFront\CloudFrontClient(["credentials" => $credentials,'region' => $region, 'version' => 'latest']);
        $cdn = "";
        $cdn_id = "";
        $success = false;

        $array = array();
        $array["Id"] = $distributionId;
        $array["IfMatch"] = $etag;

        try {
            $result = $cloudfront->deleteDistribution($array);
            $success = true;
        } catch (\Aws\CloudFront\Exception\CloudFrontException $e) {
            //$e -> getMessage();
            $error_messages[] = $e ->getAwsErrorCode();
            $error_messages[] = $e ->getAwsErrorType();
        }

        return array($success, $error_messages);
    }

    # Delete must use same parameters are current config
    
    #

    /*
      public function deploy_cloudfront($aws_region, $localurl, $site_id, $localurl, $bucketname) {
      $deployed = false;
      $error_messages = array();
      $sb_code_path = $GLOBALS["sb_code_path"];
      $aws = Aws::factory($sb_code_path . "/machine_config/aws_" . $aws_region . ".php");
      $cloudfront = $aws->get('CloudFront');
      $cdn = "";
      $cdn_id = "";
      $passed_in_bucketname = $bucketname;
      $bucketname = $bucketname . ".s3.amazonaws.com";
      #$images_path = "images/*";
      $images_path = "images/" . $site_id . "/*";

      $array = array();
      $array["Id"] = "sb_" . $site_id;

      # Try to delete any disabled distribution - in developer mode etc.
      try {
      $result = $cloudfront->deleteDistribution($array);
      } catch (\Aws\CloudFront\Exception\CloudFrontException $e) {
      #echo $e->getExceptionCode();
      #echo $e->getExceptionType();
      }

      # If there was some problem configuring the bucket.. ignore the problem and continue!
      # This will point cloudfront directly to the VPS server
      # This hasn't been tested in 2.4.3
      if ($passed_in_bucketname == null) {
      $origins = array();
      $origins["Quantity"] = 1;
      $item = array();
      $origin["Id"] = "site";

      // One or more origins doesn't exist - means the id / target ID are different!
      $origin["DomainName"] = $localurl;
      $origin["CustomOriginConfig"]["HTTPSPort"] = 443;
      $origin["CustomOriginConfig"]["HTTPPort"] = 80;
      $origin["CustomOriginConfig"]["OriginProtocolPolicy"] = "match-viewer";
      $origins["Items"] = array("1" => $origin);
      $items["Enabled"] = true;
      } else {
      $origins = array();
      $origins["Quantity"] = 2;
      $item = array();
      $first_origin["Id"] = "s3_images";
      $first_origin["DomainName"] = $bucketname;
      $first_origin["S3OriginConfig"]["OriginAccessIdentity"] = "";

      $origin["Id"] = "site";
      $domain = $localurl;
      $origin["DomainName"] = $domain;
      $origin["CustomOriginConfig"]["HTTPSPort"] = 443;
      $origin["CustomOriginConfig"]["HTTPPort"] = 80;
      $origin["CustomOriginConfig"]["OriginProtocolPolicy"] = "match-viewer";
      $origins["Items"][] = $first_origin;
      $origins["Items"][] = $origin;
      $items["Enabled"] = true;
      }

      $aliases = array();
      $aliases["Quantity"] = 0;
      $default_cache_behaviour = array();
      $default_cache_behaviour["ForwardedValues"] = array();
      $default_cache_behaviour["ForwardedValues"]["QueryString"] = true;
      $default_cache_behaviour["MinTTL"] = "18000";
      $default_cache_behaviour["TrustedSigners"]["Enabled"] = false;
      $default_cache_behaviour["TrustedSigners"]["Quantity"] = 0;
      $default_cache_behaviour["ViewerProtocolPolicy"] = "allow-all";
      $default_cache_behaviour["TargetOriginId"] = "site";
      $default_cache_behaviour["ForwardedValues"]["Cookies"] = array();
      # Do not forward cookies associated with images etc
      $default_cache_behaviour["ForwardedValues"]["Cookies"]["Forward"] = "none";



      if ($passed_in_bucketname == null) {
      $cache_behaviors = array();
      $cache_behaviors["Quantity"] = 0;
      } else {
      $cache_behaviors = array();
      $cache_behaviors["Quantity"] = 1;
      $items = array();
      $first_item["PathPattern"] = $images_path;
      $first_item["TargetOriginId"] = "s3_images";
      $first_item["ForwardedValues"]["QueryString"] = false;
      $first_item["ForwardedValues"]["Cookies"]["Forward"] = "none";
      $first_item["TrustedSigners"]["Enabled"] = false;
      $first_item["TrustedSigners"]["Quantity"] = 0;
      $first_item["ViewerProtocolPolicy"] = "allow-all";

      $first_item["MinTTL"] = "86400";
      $items[] = $first_item;
      $cache_behaviors["Items"] = $items;
      }


      #      $viewer_certificate = array();
      #     $viewer_certificate["CloudFrontDefaultCertificate"] = true;


      $comment = "Client Site";
      $logging["Bucket"] = "";
      $logging["Enabled"] = false;
      $logging["Prefix"] = "";
      $logging["IncludeCookies"] = false;
      # NOT BEHAVIOUR ... (American) BEHAVIOR lols.
      #$site_id = $id;

      # We use the most expensive price class for worldwide performance
      $array = array("CallerReference" => $site_id, "Origins" => $origins, "Aliases" => $aliases, "DefaultRootObject" => "",
      "DefaultCacheBehavior" => $default_cache_behaviour, "CacheBehaviors" => $cache_behaviors, "Comment" => $comment,
      "Logging" => $logging, "Enabled" => true, "PriceClass" => "PriceClass_All");

      # Add this if the stuff si brokened "ViewerCertificate" => $viewer_certificate

      try {
      //$result = Guzzle\Service\Resource\Model
      $result = $cloudfront->createDistribution($array);

      $cdn_id = $result->Get("Id");
      $cdn = $result->Get("DomainName");

      if ($cdn_id != null) {
      $deployed = true;
      }

      } catch (\Aws\CloudFront\Exception\CloudFrontException $e) {
      # RequestExpiredClient means we have to make system time within 5 mins of AWS time
      $error_messages[] = $e->getExceptionCode();
      $error_messages[] = $e->getExceptionType();
      $error_messages[] = $e -> getMessage();

      # This counts as success in test mode.
      if ($e->getExceptionCode() == "DistributionAlreadyExists") {
      #$deployed = true;
      $cdn = "DistributionAlreadyExists";
      }

      }

      return array($deployed, $error_messages, $cdn, $cdn_id);
      } */

    /*
      public function disable_cloudfront($aws_region, $site_id, $distributionId) {
      $deployed = false;
      $error_messages = array();
      $sb_code_path = $GLOBALS["sb_code_path"];
      $aws = Aws::factory($sb_code_path . "/machine_config/aws_" . $aws_region . ".php");
      $cloudfront = $aws->get('CloudFront');

      $status = "";

      $array = array();
      $id = "sb_" . $site_id;
      $array["Id"] = $distributionId;
      $etag = "";
      try {
      $result = $cloudfront->getDistribution($array);

      #echo "Getting dist";



      $etag = $result->get("ETag");
      #echo $etag;
      } catch (\Aws\CloudFront\Exception\CloudFrontException $e) {
      $error_messages[] = $e->getExceptionCode();
      $error_messages[] = $e->getExceptionType();
      $error_messages[] = $e -> getMessage();
      }

      if ($etag != "") {

      $origins = array();
      $origins["Quantity"] = 1;
      $item = array();
      $origin["Id"] = "site";

      $origin["DomainName"] = "disabledcdn.shopsbee.com";
      $origin["CustomOriginConfig"]["HTTPSPort"] = 443;
      $origin["CustomOriginConfig"]["HTTPPort"] = 80;
      $origin["CustomOriginConfig"]["OriginProtocolPolicy"] = "match-viewer";
      $origins["Items"] = array("1" => $origin);
      $items["Enabled"] = false;




      $aliases = array();
      $aliases["Quantity"] = 0;
      $default_cache_behaviour = array();
      $default_cache_behaviour["ForwardedValues"] = array();
      $default_cache_behaviour["ForwardedValues"]["QueryString"] = true;
      $default_cache_behaviour["MinTTL"] = "18000";
      $default_cache_behaviour["TrustedSigners"]["Enabled"] = false;
      $default_cache_behaviour["TrustedSigners"]["Quantity"] = 0;
      $default_cache_behaviour["ViewerProtocolPolicy"] = "allow-all";
      $default_cache_behaviour["TargetOriginId"] = "site";
      $default_cache_behaviour["ForwardedValues"]["Cookies"] = array();
      # Do not forward cookies associated with images etc
      $default_cache_behaviour["ForwardedValues"]["Cookies"]["Forward"] = "none";

      $cache_behaviors = array();
      $cache_behaviors["Quantity"] = 0;


      $viewer_certificate = array();
      $viewer_certificate["CloudFrontDefaultCertificate"] = true;

      $comment = "Client Site";
      $logging["Bucket"] = "";
      $logging["Enabled"] = false;
      $logging["Prefix"] = "";
      $logging["IncludeCookies"] = false;
      # NOT BEHAVIOUR ... (American) BEHAVIOR lols.
      $array = array("CallerReference" => $site_id, "Origins" => $origins, "Aliases" => $aliases, "DefaultRootObject" => "",
      "DefaultCacheBehavior" => $default_cache_behaviour, "CacheBehaviors" => $cache_behaviors, "Comment" => $comment,
      "Logging" => $logging, "PriceClass" => "PriceClass_All",  "Enabled" => false, "ViewerCertificate" => $viewer_certificate ,"Id" => $distributionId, "IfMatch" => $etag);

      try {
      #echo "updating";
      //$result = Guzzle\Service\Resource\Model
      $result = $cloudfront->updateDistribution($array);

      #echo "Gettign status";

      # Waiting for "Deployed" Status
      # Well.. we're meant to wait anyhow! Otherwise we can't delete.
      $status = $result->Get("Status");
      } catch (\Aws\CloudFront\Exception\CloudFrontException $e) {
      # RequestExpiredClient means we have to make system time within 5 mins of AWS time
      $error_messages[] = $e->getExceptionCode();
      $error_messages[] = $e->getExceptionType();
      $error_messages[] = $e -> getMessage();
      #$error_messages[] = $e -> message;
      }

      }
      return array($deployed, $error_messages, $status, $etag);
      } */


}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
