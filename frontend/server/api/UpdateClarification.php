<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 * 
 * POST /contests/update/:id
 * Si el usuario puede verlo, actualiza la clarificación ID.
 *
 * */

require_once("ApiHandler.php");

class UpdateClarification extends ApiHandler
{
    
    protected function RegisterValidatorsToRequest()
    {
        ValidatorFactory::numericValidator()->addValidator(new CustomValidator(
            function ($value)
            {
                // Check if the contest exists
                return ClarificationsDAO::getByPK($value);
            }, "Clarification requested is invalid."))
        ->validate(RequestContext::get("clarification_id"), "clarification_id");
        
        ValidatorFactory::stringNotEmptyValidator()->validate(RequestContext::get("answer"), "answer");
        
        ValidatorFactory::numericValidator()->validate(RequestContext::get("public"), "public");                
                                
        // Only contest director or problem author are allowed to update clarifications
        $clarification = ClarificationsDAO::getByPK(RequestContext::get("clarification_id"));        
        $contest = ContestsDAO::getByPK($clarification->getContestId());                        
	$problem = ProblemsDAO::getByPK($clarification->getProblemId());

        if(!(Authorization::IsContestAdmin($this->_user_id, $contest) || 
                $problem->getAuthorId() === $this->_user_id ))
        {            
            throw new ApiException(ApiHttpErrors::forbiddenSite());
        }        
    }   

    protected function GenerateResponse() 
    {
                
        // Get our clarificatoin given the id
        try
        {            
            $clarification = ClarificationsDAO::getByPK(RequestContext::get("clarification_id"));
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );                
        }
        
        // Update clarification        
	// The clarificator may opt to modify the message (typos)
	if (RequestContext::get("message")) {
	        $clarification->setMessage(RequestContext::get("message"));                
	}
        $clarification->setAnswer(RequestContext::get("answer"));
        $clarification->setPublic(RequestContext::get("public"));                
        
        // Let DB handle time update
        $clarification->setTime(NULL);
                
        // Save the clarification
        try
        {            
            ClarificationsDAO::save($clarification);
        }
        catch( Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );         
        }
        
        // Happy ending
        $this->addResponse("status", "ok");
    }    
}

?>
