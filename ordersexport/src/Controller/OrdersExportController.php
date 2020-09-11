<?php
namespace OrdersExport\Controller;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

use Db;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TaskType extends AbstractType

{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $shoppingsMethodsDb = (Db::getInstance()->executeS('
            SELECT s.id_order_state id, s.name state
FROM ps_order_state_lang s
WHERE s.id_lang = 1
            '));
        $shoppingsMethods = array();
        $shoppingsMethods[0] = 'wszystkie';
        foreach($shoppingsMethodsDb as $s) $shoppingsMethods[$s['id']] = $s['state'];
        $shoppingsMethods = array_flip($shoppingsMethods);

        $builder
            ->add('fromDate', DateType::class, [
                'label' => 'Od',
            ])
            ->add('dueDate', DateType::class, [
                'label' => 'Do',
            ])
            ->add('state', ChoiceType::class, [
                    'label' => 'Status płatności',
                    'choices'  => $shoppingsMethods,

                ]
            )
            ->add('createAs', ChoiceType::class, [
                    'label' => 'Wygeneruj jako',
                    'choices'  => [
                        'Wyświetl zestawienie poniżej' => true,
                        'Wygeneruj plik csv' => false,
                    ],
                    'choices_as_values' => true
                ]
            )

            ->add('save', SubmitType::class, [
                'label' => 'Wygeneruj',
            ])

        ;
    }
}
class Task
{
    protected $fromDate;
    protected $dueDate;
    protected $createAs;
    protected $state;

    public function getState(){
        return $this->state;
    }

    public function setState($state){
        $this->state = $state;
    }

    public function getCreateAs(){
        return $this->createAs;
    }

    public function setCreateAs($createAs){
        $this->createAs = $createAs;
    }

    public function getFromDate()
    {
        return $this->fromDate;
    }

    public function setFromDate(\DateTime $fromDate = null)
    {
        $this->fromDate = $fromDate;
    }

    public function getDueDate()
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTime $dueDate = null)
    {
        $this->dueDate = $dueDate;
    }
}

class OrdersExportController extends FrameworkBundleAdminController{

    public function printForm(Request $request){
        $task = new Task();
        $task->setFromDate(new \DateTime('first day of this month'));
        $task->setDueDate(new \DateTime('today'));

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            $selectedState = 0;
            if($task->getState()>0) $selectedState = " AND o.current_state = ".$task->getState();
            $fromDate = $task->getFromDate();
            $dueDate = $task->getDueDate();
            $range = " AND o.date_add >= '".$fromDate->format('Y-m-d')."' AND o.date_add <= '".$dueDate->format('Y-m-d')."'";

            if($task->getCreateAs()) {

                $summaryDb = (Db::getInstance()->executeS('
            SELECT sum(o.total_paid) paid, sum(o.total_shipping) shipping, count(d.product_id) products
FROM ps_orders o
JOIN ps_order_state_lang s ON s.id_order_state = o.current_state
JOIN ps_order_detail d ON o.id_order = d.id_order
JOIN ps_address a ON o.id_address_invoice = a.id_address
WHERE s.id_lang = 1'.$selectedState.$range));

                $ordersSum = $summaryDb[0]['paid'];
                $shippingSum = $summaryDb[0]['shipping'];
                $soldTotal = $summaryDb[0]['products'];

                return $this->render('@Modules/ordersexport/templates/admin/export.html.twig', [
                    'form' => $form->createView(),
                    'ordersSum' => $ordersSum,
                    'shippingSum' => $shippingSum,
                    'soldTotal' => $soldTotal,
                    'downloadFile' => '',
                ]);
            };

            if(!$task->getCreateAs()){
                $toFile = (Db::getInstance()->executeS('
            SELECT o.id_order id, o.date_add date, s.name state, o.total_paid paid, o.total_shipping shipping, count(d.product_id) products, a.city city
FROM ps_orders o
JOIN ps_order_state_lang s ON s.id_order_state = o.current_state
JOIN ps_order_detail d ON o.id_order = d.id_order
JOIN ps_address a ON o.id_address_invoice = a.id_address
WHERE s.id_lang = 1 '.$selectedState.$range.' 
GROUP BY d.id_order'));

                $fileContent = "id;date;state;paid;shipping;city\n";
                foreach($toFile as $row){
                    $rowToFile = "";
                    foreach($row as $item){
                        $rowToFile.=$item.";";
                    }
                    $rowToFile = substr($rowToFile,0,strlen($rowToFile)-1);
                    $fileContent.=$rowToFile."\n";
                }
                $fileContent = iconv("CP1257","UTF-8", $fileContent);

                file_put_contents( '../upload/export.csv',$fileContent);

                return $this->render('@Modules/ordersexport/templates/admin/export.html.twig', [
                    'form' => $form->createView(),
                    'ordersSum' => '',
                    'shippingSum' => '',
                    'soldTotal' => '',
                    'downloadFile' => __PS_BASE_URI__  .('/upload/export.csv'),
                ]);

            }

        }

        return $this->render('@Modules/ordersexport/templates/admin/export.html.twig', [
            'form' => $form->createView(),
            'ordersSum' => '',
            'shippingSum' => '',
            'soldTotal' => '',
            'downloadFile' => ''
        ]);


    }
}
