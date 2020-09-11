<?php
include_once "../config/autoload.php";

if (!defined('_PS_VERSION_'))
    exit;

class exitpopup extends Module
{
    public function __construct(){
        $this->name = 'exitpopup';
        $this->tab = 'Exit Popup Window';
        $this->version = '1.0.0';
        $this->author = 'Arkadiusz Tomczak';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = 1;

        parent::__construct();

        $this->displayName = $this->l('Exit Popup');
        $this->description = $this->l('Pokaż okienko typu Exit Popup. Zadanie rekrutacyjne Convertis nr 1.');

        $this->confirmUninstall = $this->l('Czy chcesz usunąć moduł?');
        $this->templateFile = 'module:exitpopup/exitpopup.tpl';
    }

    public function install()
    {
        return parent::install()
            && Configuration::updateValue('EXITPOPUP_HEADER', 'Wait!')
            && Configuration::updateValue('EXITPOPUP_BUTTON', 'Return to site')
            && Configuration::updateValue('EXITPOPUP_CONTENT', '<p style="font-size:20px">We want to give you a<br><strong>10% discount</strong> for your first order.</p><br><p style="font-size:12px"><strong>Use this discount code</strong> at the checkout - <strong>BH10</strong></p>', true)
            && Configuration::updateValue('EXITPOPUP_BTN', 'Return to site')
            && Configuration::updateValue('EXITPOPUP_BG', '#f5375d')
            && Configuration::updateValue('EXITPOPUP_FONTCOLOR', '#fefeff')
            && Configuration::updateValue('EXITPOPUP_IMG', 'stop.png')
            && Configuration::updateValue('EXITPOPUP_FROM', '2020-01-01')
            && Configuration::updateValue('EXITPOPUP_TO', '2023-12-31')
            && $this->registerHook('displayFooter')
            && $this->registerHook('displayHeader')
            && $this->registerHook('header');
    }

    public function hookDisplayHeader(array $params)
    {
        $this->context->controller->registerStylesheet('modules-exitpopup', 'modules/exitpopup/views/css/exitpopup.css', ['media' => 'all', 'priority' => 150]);
        $this->context->controller->addJS(($this->_path).'views/js/exitpopup.js');
    }
    
    public function hookDisplayFooter(array $params){

        $from = strtotime(Tools::getValue('EXITPOPUP_FROM', Configuration::get('EXITPOPUP_FROM')));
        $to = strtotime(Tools::getValue('EXITPOPUP_TO', Configuration::get('EXITPOPUP_TO')));
        $now = time();

        if(($now >= $from) && ($now <= $to)) {
            $header = Tools::getValue('EXITPOPUP_HEADER', Configuration::get('EXITPOPUP_HEADER'));
            $button = Tools::getValue('EXITPOPUP_BUTTON', Configuration::get('EXITPOPUP_BUTTON'));
            $content = Tools::getValue('EXITPOPUP_CONTENT', Configuration::get('EXITPOPUP_CONTENT'));
            $bgColor = Tools::getValue('EXITPOPUP_BG', Configuration::get('EXITPOPUP_BG'));
            $fontColor = Tools::getValue('EXITPOPUP_FONTCOLOR', Configuration::get('EXITPOPUP_FONTCOLOR'));
            $categories = unserialize(Tools::getValue('EXITPOPUP_SHOWINCATEGORIES', Configuration::get('EXITPOPUP_SHOWINCATEGORIES')));
            $img = $this->_path . "img/" . Tools::getValue('EXITPOPUP_IMG', Configuration::get('EXITPOPUP_IMG'));

            $this->context->smarty->assign('header', $header);
            $this->context->smarty->assign('button', $button);
            $this->context->smarty->assign('content', $content);
            $this->context->smarty->assign('bgColor', $bgColor);
            $this->context->smarty->assign('fontColor', $fontColor);
            $this->context->smarty->assign('img', $img);
            $this->context->smarty->assign('categories', $categories);
            $this->context->controller->addJS($this->path.'views/js/exitpopup.js');
            return $this->display(__FILE__, 'exitpopup.tpl');
        }
    }

    public function uninstall()
    {
        if (!parent::uninstall())
            return false;
        return true;
    }

    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit'.$this->name)) {
            $header = strval(Tools::getValue('EXITPOPUP_HEADER'));
            $button = strval(Tools::getValue('EXITPOPUP_BUTTON'));
            $content = (Tools::getValue('EXITPOPUP_CONTENT'));
            $bgColor = strval(Tools::getValue('EXITPOPUP_BG'));
            $fontColor = strval(Tools::getValue('EXITPOPUP_FONTCOLOR'));;
            $timeFrom = strval(Tools::getValue('EXITPOPUP_FROM'));
            $timeTo = strval(Tools::getValue('EXITPOPUP_TO'));
            $categories = strval(serialize(Tools::getValue('EXITPOPUP_CATEGORIES')));

            if (!$header || empty($header) ||  !Validate::isGenericName($header)) $output .= $this->displayError($this->l('Nieprawidłowy tytuł'));
            elseif (!$button || empty($button) ||  !Validate::isGenericName($header)) $output .= $this->displayError($this->l('Nieprawidłowy tekst na przycisku'));
            elseif (!$content || empty($content)) $output .= $this->displayError($this->l('Nieprawidłowa treść komunikatu'));
            elseif (empty($bgColor) || !$bgColor || !Validate::isColor($bgColor)) $output .= $this->displayError($this->l('Nieprawidłowy kolor tła'));
            elseif (empty($fontColor) || !$fontColor || !Validate::isColor($fontColor)) $output .= $this->displayError($this->l('Nieprawidłowy kolor czcionki'));
            elseif (!$timeFrom || !$timeTo || empty($timeFrom) || empty($timeTo) || strtotime($timeFrom) > strtotime($timeTo)) $output .= $this->displayError($this->l('Nieprawidłowy okres wyświetlania komunikatu'));
            else {
                Configuration::updateValue('EXITPOPUP_HEADER', $header);
                Configuration::updateValue('EXITPOPUP_BUTTON', $button);
                Configuration::updateValue('EXITPOPUP_CONTENT', $content, true);
                Configuration::updateValue('EXITPOPUP_BG', $bgColor);
                Configuration::updateValue('EXITPOPUP_FONTCOLOR', $fontColor);
                Configuration::updateValue('EXITPOPUP_FROM', $timeFrom);
                Configuration::updateValue('EXITPOPUP_TO', $timeTo);
                Configuration::updateValue('EXITPOPUP_SHOWINCATEGORIES', $categories);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }

            if (isset($_FILES['EXITPOPUP_UIMG'])
                && isset($_FILES['EXITPOPUP_UIMG']['tmp_name'])
                && !empty($_FILES['EXITPOPUP_UIMG']['tmp_name'])) {
                if ($error = ImageManager::validateUpload($_FILES['EXITPOPUP_UIMG'], 4000000)) {
                    return $error;
                } else {

                    $file_name = md5('background.png').'.png';

                    if (!move_uploaded_file($_FILES['EXITPOPUP_UIMG']['tmp_name'], dirname(__FILE__).DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.$file_name)) {
                        return $this->displayError($this->trans('An error occurred while attempting to upload the file.', array(), 'Admin.Notifications.Error'));
                    } else {
                        unlink($this->_path."img/".Tools::getValue('EXITPOPUP_IMG', Configuration::get('EXITPOPUP_IMG')));
                        Configuration::updateValue('EXITPOPUP_IMG', $file_name);
                    }
                }
            }
        }

        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        $categories = Category::getCategories( (int)($cookie->id_lang), true, false  ) ;
        $categoriesTree = array();

        foreach ($categories as $c){
            $i['key'] = $c['id_category'];
            $i['name'] = $c['name'];
            array_push($categoriesTree,$i);
        }

        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Ustawienia podstawowe'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Nagłówek komunikatu'),
                    'name' => 'EXITPOPUP_HEADER',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'textarea',

                    'label' => $this->l('Treść komunikatu'),
                    'name' => 'EXITPOPUP_CONTENT',
                    'rows' => 10,
                    'cols' => 100,
                    'autoload_rte' => true,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Tekst na przycisku'),
                    'name' => 'EXITPOPUP_BUTTON',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Kolor tła'),
                    'name' => 'EXITPOPUP_BG'
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Kolor czcionki'),
                    'name' => 'EXITPOPUP_FONTCOLOR'
                ],
                [
                    'type' => 'file',
                    'label' => $this->l('Obrazek'),
                    'name' => 'EXITPOPUP_UIMG',
                    'desc' => 'Aktualny obrazek: <br><img style="background: url(\''.$this->_path.'img/checkerboard-pattern.png\')  0px 0px/30px 30px" src="'.$this->_path."img/".Tools::getValue('EXITPOPUP_IMG', Configuration::get('EXITPOPUP_IMG')).'">',
                    'lang' => true,
                ],
            ]
        ];
        $fieldsForm[1]['form'] = [
            'legend' => [
                'title' => $this->l('Zakres wyświetlania'),
            ],
            'input' => [
                [
                    'type' => 'date',
                    'label' => $this->l('Rozpocznij wyświetlanie od:'),
                    'name' => 'EXITPOPUP_FROM',
                    'lang' => true,
                ],
                [
                    'type' => 'date',
                    'label' => $this->l('Zakończ wyświetlanie do:'),
                    'name' => 'EXITPOPUP_TO',
                    'desc' => 'Wyświetl komunikat tylko w podanych okresie',
                    'lang' => true,
                ],
                [
                    'type' => 'select',
                    'name' => 'EXITPOPUP_CATEGORIES[]',
                    'label' => $this->l('Ogranicz do wybranych kategorii'),
                    'desc' => $this->l('Komunikat wyświetli się tylko na stronach wybranych kategorii.'),
                    'multiple' => true,
                    'options' => array(
                        'query' => $categoriesTree,
                        'id' => 'key',
                        'name' => 'name'
                    )
                ]
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                    '&token='.Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            ]
        ];

        $helper->fields_value['EXITPOPUP_HEADER'] = Tools::getValue('EXITPOPUP_HEADER', Configuration::get('EXITPOPUP_HEADER'));
        $helper->fields_value['EXITPOPUP_BUTTON'] = Tools::getValue('EXITPOPUP_BUTTON', Configuration::get('EXITPOPUP_BUTTON'));
        $helper->fields_value['EXITPOPUP_CONTENT'] = Tools::getValue('EXITPOPUP_CONTENT', Configuration::get('EXITPOPUP_CONTENT'));
        $helper->fields_value['EXITPOPUP_BG'] = Tools::getValue('EXITPOPUP_BG', Configuration::get('EXITPOPUP_BG'));
        $helper->fields_value['EXITPOPUP_FONTCOLOR'] = Tools::getValue('EXITPOPUP_FONTCOLOR', Configuration::get('EXITPOPUP_FONTCOLOR'));
        $helper->fields_value['EXITPOPUP_IMG'] = strval(Tools::getValue('EXITPOPUP_IMG', Configuration::get('EXITPOPUP_IMG')));
        $helper->fields_value['EXITPOPUP_FROM'] = Tools::getValue('EXITPOPUP_FROM', Configuration::get('EXITPOPUP_FROM'));
        $helper->fields_value['EXITPOPUP_TO'] = Tools::getValue('EXITPOPUP_TO', Configuration::get('EXITPOPUP_TO'));
        $helper->fields_value['EXITPOPUP_CATEGORIES[]'] = unserialize(Tools::getValue('EXITPOPUP_SHOWINCATEGORIES', Configuration::get('EXITPOPUP_SHOWINCATEGORIES')));

        return $helper->generateForm($fieldsForm);
    }
}