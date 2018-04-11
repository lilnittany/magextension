<?php

namespace BORN\OrderController\Controller\Index;

use Magento\Framework\App\Action\Context;
use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use \Magento\Framework\Controller\Result\JsonFactory;

class Order extends \Magento\Framework\App\Action\Action
{

    protected $_orderCollectionFactory;
    protected $_customerSession;
    protected $_orderConfig;
    protected $orders;
    protected $_orders;
    protected $count;
    private $orderCollectionFactory;

    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }


    private function getOrderCollectionFactory()
    {
        if ($this->orderCollectionFactory === null) {
            $this->orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
        }
        return $this->orderCollectionFactory;
    }

    public function getOrders($email,$order)
    {
        if (isset($order) && $order != "") {
			if (!$this->orders) {
				$this->orders = $this->getOrderCollectionFactory()->create(
				)->addFieldToSelect(
					'*'
				)->addFieldToFilter(
					'entity_id',
					$order
				)->addFieldToFilter(
					'customer_email',
					$email
				)->setOrder(
					'created_at',
					'desc'
				);
			}
		} else {
			if (!$this->orders) {
				$this->orders = $this->getOrderCollectionFactory()->create(
				)->addFieldToSelect(
					'*'
				)->addFieldToFilter(
					'customer_email',
					$email
				)->setOrder(
					'created_at',
					'desc'
				);
			}
		}
        return $this->orders;
    }

    public function execute()
    {

        if (!isset($_POST['email'])) {
        	$email = "roni_cost@example.com"; //dummy data for testing
        	$emailtext = "Email Address: $email ";
        } else {
        	$email = $_POST['email'];
        	$emailtext = "Email Address: $email ";
        }
        if (!isset($_POST['order']) || $_POST['order'] == "") {
        	$order = "";
        	$ordertext = "";
        } else {
        	$order = $_POST['order'];
        	$ordertext = "<br>Order Number: $order ";
        }

        $_orders = $this->getOrders($email,$order);
        if (sizeof($_orders->getItems()) > 0) {
        	$count = sizeof($_orders->getItems());
        } else {
        	$count = "0";
        }

$template = '<strong style="font-size:18px">Your Results</strong><br>';
$template .= "<strong>$emailtext $ordertext</strong><p><br>";
$template .= '
<div class="block block-dashboard-orders">
    <div class="block-title order">
    </div>
    <div class="block-content">';

if (sizeof($_orders->getItems()) > 0) {
	$template .= '
        <div class="table-wrapper orders-recent">
            <table class="data table table-order-items recent" id="my-orders-table">
                <thead>
                    <tr>
                        <th scope="col" class="col id">Order #</th>
                        <th scope="col" class="col date">Date</th>
                        <th scope="col" class="col shipping">Ship To</th>
                        <th scope="col" class="col total">Order Total</th>
                        <th scope="col" class="col status">Status</th>
                        <th scope="col" class="col actions">Action</th>
                    </tr>
                </thead>
                <tbody>';

foreach ($_orders as $_order) {
	$template .= '<tr>
                            <td data-th="Order #" class="col id">';
    $template .= $_order->getRealOrderId();
    $template .= '</td>
                            <td data-th="Date" class="col date">';
    $template .= $_order->getCreatedAt();
    $template .= '</td>
                            <td data-th="Ship To" class="col shipping">';
    $template .= $_order->getShippingAddress()->getName();
    $template .= '</td>
                            <td data-th="Order Total" class="col total">';
    $template .= $_order->formatPrice($_order->getGrandTotal());
    $template .= '</td>
                            <td data-th="Status" class="col status">';
    $template .= $_order->getStatusLabel();
    $template .= '</td>
                            <td data-th="Actions" class="col actions">
                            </td>
                        </tr>';
}

    $template .= '</tbody>
            </table>
        </div>';
} else {
	$template .= '
        <div class="message info empty"><span>There are no guest orders associated with that information. Please try again</span></div>';
}
$template .= '
</div>
</div>
';

        $response = $this->resultFactory
		    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
		    ->setData([
		        'status'  => "Success",
		        'count'   => $count,
		        'template'=> $template,
		        'message' => "Orders retrieved successfully"
		    ]);

		return $response;

    }
}