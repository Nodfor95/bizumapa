<?php

class CustomSubscriptionInterface
{

	// Fluxo de Assinatura
	// https://pagarme.zendesk.com/hc/pt-br/articles/360000215732-Fluxo-de-cobran%C3%A7a-de-uma-assinatura-

	public static $VALID   = 'VALID';
	public static $INVALID = 'INVALID';
	public static $CHECK   = 'CHECK';

	public static $STATUS_PAID      = 'paid';
	public static $STATUS_ENDED     = 'ended';
	public static $STATUS_UNPAID    = 'unpaid';
	public static $STATUS_PENDING   = 'pending_payment';
	public static $STATUS_TRIALING  = 'trialing';
	public static $STATUS_CANCELED  = 'canceled';

	public static $BR_STATUS_PAID      = 'Pago';
	public static $BR_STATUS_ENDED     = 'Finalizado';
	public static $BR_STATUS_UNPAID    = 'Não Pago';
	public static $BR_STATUS_PENDING   = 'Pagamento Pendente';
	public static $BR_STATUS_TRIALING  = 'Período de Avaliação';
	public static $BR_STATUS_CANCELED  = 'Cancelada';

	public static $METHOD_CARD    = 'credit_card';
	public static $METHOD_BOLETO  = 'boleto';

	public static $BR_METHOD_CARD    = 'Cartão de Crédito';
	public static $BR_METHOD_BOLETO  = 'Boleto';

	public static $TRIAL_PERIOD = '+1 day';
	public static $SUBSC_PERIOD = '+1 day';

	public static function getClient()
	{
		return new PagarMe\Client(API_KEY);
	}

	public static function getCookie()
	{
		return TSession::getValue('subscription');
	}

	public static function setCookie($value)
	{
		TSession::setValue('subscription', $value );
	}	

	public static function checkSubscription($obj=null)
	{

		// TODO:  DESLOGAR USUÁRIO, SE NÃO, É SÓ DEIXAR LOGADO QUE NUNCA VAI 
		// RETORNAR FALSE

		if (CustomApplicationUtils::isAdmin()) {
			return TRUE;
		}

		$is_trial_valid = CustomSubscriptionInterface::checkExpirationDate(
				TSession::getValue('usercreation'),
				CustomSubscriptionInterface::$TRIAL_PERIOD);

		if ($is_trial_valid) {

			if ($obj) {
				$days = CustomSubscriptionInterface::getFreeTrialDays();
				$alert = CustomSubscriptionInterface::getAlertHTML($days);
				$obj->addElement($alert);
			}

			return TRUE;
		}

		$subsc = CustomSubscriptionInterface::getCookie();

		switch ($subsc) {
			case CustomSubscriptionInterface::$VALID:
				$isvalid = TRUE;
				break;
			case CustomSubscriptionInterface::$INVALID:
				$isvalid = FALSE;
				break;
			case CustomSubscriptionInterface::$CHECK:
				$isvalid = CustomSubscriptionInterface::checkValidation();
				break;							
			default:
				$isvalid = CustomSubscriptionInterface::checkValidation();
				break;
		}

		if ($isvalid) {

			CustomSubscriptionInterface::setCookie(CustomSubscriptionInterface::$VALID);

		} else {

			CustomSubscriptionInterface::setCookie(CustomSubscriptionInterface::$INVALID);
			
		}

		return $isvalid;
		
	}

	public static function checkExpirationDate($start_date, $period)
	{
		$period_start = new DateTime($start_date);
		$period_expire = $period_start->modify($period);
		$now = new DateTime();
		$is_expired = ($now > $period_expire) ? FALSE : TRUE ;
		return $is_expired;
	}	

	public static function checkValidation()
	{

		$subsc = CustomSubscriptionInterface::getUserSubscription();

		if (is_null($subsc)) { return FALSE; }

		$subsc_obj = CustomSubscriptionInterface::getSubscriptionObj($subsc);

		switch ($subsc_obj->status) {

			case CustomSubscriptionInterface::$STATUS_PAID:
				return TRUE;

			case CustomSubscriptionInterface::$STATUS_CANCELED:
				$is_valid = CustomSubscriptionInterface::checkExpirationDate(
					$subsc_obj->current_period_start,
					CustomSubscriptionInterface::$SUBSC_PERIOD
				);
				if (!$is_valid) { 
					CustomSubscriptionInterface::setUserSubscription(null); 
				}
				return $is_valid;
			
			default:
				return FALSE;
		}
		
	}

	public static function createSubscription($data)
	{
		$pagarme = CustomSubscriptionInterface::getClient();
    	$data['customer']['external_id'] = TSession::getValue('userid');
    	$data['plan_id'] = PLAN_ID;

		try {

			return $pagarme->subscriptions()->create($data);
			
		} catch (Exception $e) {

			CustomApplicationUtils::exceptionHandler($e);
		}

	}

	public static function getUserSubscription()
	{
        try 
        { 
            TTransaction::open(DEFAULT_DB); 
			$user = new SystemUser(TSession::getValue('userid'));
            TTransaction::close(); 
            return $user->subscription;
        } 
        catch (Exception $e) 
        { 
			CustomApplicationUtils::exceptionHandler($e);
        }

	}

	public static function setUserSubscription($id)
	{
        try 
        { 
            TTransaction::open(DEFAULT_DB); 
			$user = new SystemUser(TSession::getValue('userid'));
			$user->subscription = $id;
			$user->store();
            TTransaction::close(); 
            return $user;
        } 
        catch (Exception $e) 
        { 
            TTransaction::rollback();
			CustomApplicationUtils::exceptionHandler($e);
        }

	}

	public static function getSubscriptionObj($id)
	{

		$id = (string) $id;
		$pagarme = CustomSubscriptionInterface::getClient();

		if (empty($id)) {
			return;
		}

		try {	

			return $pagarme->subscriptions()->get(['id' => $id]);

		} catch (Exception $e) {
			CustomApplicationUtils::exceptionHandler($e);
		}

	}

	public static function getTransactionsObj($id)
	{

		$pagarme = CustomSubscriptionInterface::getClient();

		try {

			return  $pagarme->subscriptions()->transactions([
	    		'subscription_id' => (string) $id
			]);
			
		} catch (Exception $e) {

			CustomApplicationUtils::exceptionHandler($e);
		
		}
	}

	public static function getFreeTrialDays()
	{				
		$start = new DateTime(TSession::getValue('usercreation'));
		$now = new DateTime();
		$diff = $start->diff($now);	
		return 5 - $diff->d;
	}

	public static function getAlertHTML($days)
	{
		$alert = '<div class="talert alert alert-dismissible alert-info" role="alert">
					<div style="text-align:center"><b>Você possui mais %days% dias grátis. <a href="index?class=CustomSubscriptionForm">VER ASSINATURA</a></b></div>
			 	  </div>';

		return str_replace('%days%', $days, $alert);
	}

	public static function translate($value)
	{
		$class_vars = get_class_vars(__CLASS__);
		$key = array_search($value, $class_vars);
		$key = 'BR_' . $key;
		return $class_vars[$key];
	}

}