<?php
use \Firebase\JWT\JWT;

/**
 * SystemPasswordResetForm
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class PasswordResetForm extends TPage
{
    protected $form; // form
    protected $jwt;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct();

        new TMessage('info', 'Bem-vindo(a) ao Sistema de Pesquisa de Preço do Procon (SPD). Verificamos que esse é seu primeiro Login, altere a 
                    sua senha para sua maior segurança.');
        $ini  = AdiantiApplicationConfig::get();

        $this->style = 'clear:both';
        // creates the form
        $this->form = new BootstrapFormBuilder('form_login');
        $this->form->setFormTitle( _t('Reset password') );
        $this->form->style="margin-left:17% ";

        // create the form fields
        $jwt = new THidden('jwt');
        $password1 = new TPassword('password1');
        $password2 = new TPassword('password2');

        // define the sizes
        $password1->setSize('60%', 80);
        $password2->setSize('60%', 80);

        $locker = '<span style="float:left;margin-left:44px;height:35px;" class="login-avatar"><span class="glyphicon glyphicon-lock"></span></span>';
        $password1->style = 'height:35px; font-size:14px;float: left;border-bottom-left-radius: 0;border-top-left-radius: 0;';
        $password2->style = 'height:35px; font-size:14px;float: left: center;border-bottom-left-radius: 0;border-top-left-radius: 0;';

        $password1->placeholder = _t('Password');
        $password2->placeholder = _t('Password confirmation');

        $this->form->addFields( [$jwt] );
        $this->form->addFields( [$locker, $password1] );
        $this->form->addFields( [$locker, $password2] );

        $btn = $this->form->addAction(_t('Send'), new TAction(array($this, 'onReset')), '');
        $btn->class = 'btn btn-primary';
        $btn->style = 'height: 40px;width: 30%;display: block;font-size:17px;margin-left: 21%';

        $wrapper = new TElement('div');
        $wrapper->style = 'margin:auto; margin-top:100px;max-width:70%;';
        $wrapper->id    = 'login-wrapper';
        $wrapper->add($this->form);

        // add the form to the page
        parent::add($wrapper);
        $this->onLoad($param);
    }

    /**
     * Form load
     */

    public function onLoad($param)
    {
        $ini = AdiantiApplicationConfig::get();

        try
        {
            if ($ini['permission']['reset_password'] !== '1')
            {
                throw new Exception( _t('The password reset is disabled') );
            }

            if (empty($ini['general']['seed']) OR $ini['general']['seed'] == 's8dkld83kf73kf094')
            {
                throw new Exception(_t('A new seed is required in the application.ini for security reasons'));
            }

            TTransaction::open('permission');

            $login = TSession::getValue('login');
            $user  = SystemUser::newFromLogin($login);

            if ($user instanceof SystemUser)
            {
                if ($user->active == 'N')
                {
                    throw new Exception(_t('Inactive user'));
                }
                else
                {
                    $key = APPLICATION_NAME . $ini['general']['seed'];

                    $token = array(
                        "user" => $user->login,
                        "expires" => strtotime("+ 3 hours")
                    );

                    $this->jwt = JWT::encode($token, $key);

                    $referer = $_SERVER['HTTP_REFERER'];
                    $url = substr($referer, 0, strpos($referer, 'index.php'));
                    $url .= 'index.php?class=SystemPasswordResetForm&method=onLoad&jwt='.$this->jwt;

                    $replaces = [];
                    $replaces['name'] = $user->name;
                    $replaces['link'] = $url;
                    $html = new THtmlRenderer('app/resources/system_reset_password.html');
                    $html->enableSection('main', $replaces);

                }
            }
            else
            {
                throw new Exception(_t('User not found'));
            }
        }
        catch (Exception $e)
        {
            new TMessage('error',$e->getMessage());
            TTransaction::rollback();
        }

        /*$data = new stdClass;
        $data->jwt = $param['jwt'];
        $this->form->setData($data);
        */
    }

    public function onReset($param)
    {
        $ini = AdiantiApplicationConfig::get();

        try
        {
            if (empty($param['password1']))
            {
                throw new Exception('Senha vazia');
            }

            if( $param['password1'] !== $param['password2'] )
            {
                throw new Exception(_t('The passwords do not match'));
            }

            if (empty($ini['general']['seed']) OR $ini['general']['seed'] == 's8dkld83kf73kf094')
            {
                throw new Exception(_t('A new seed is required in the application.ini for security reasons'));
            }

            $key = APPLICATION_NAME . $ini['general']['seed'];

            $token = (array) JWT::decode($this->jwt, $key, array('HS256'));

            $login = $token['user'];
            $expires = $token['expires'];

            if ($expires < strtotime('now'))
            {
                throw new Exception('Token expired. This operation is not allowed');
            }

            TTransaction::open('permission');
            $user  = SystemUser::newFromLogin($login);

            if ($user instanceof SystemUser)
            {
                if ($user->active == 'N')
                {
                    throw new Exception(_t('Inactive user'));
                }
                else
                {
                    $user->password = md5($param['password1']);
                    $user->frontpage_id = 55;
                    $user->store();

                    new TMessage('info', _t('The password has been changed'));
                    LoginForm::onLogout();

                }
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error',$e->getMessage());
            TTransaction::rollback();
        }
    }
}
