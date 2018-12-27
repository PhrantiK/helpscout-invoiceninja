<?php

require 'vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../../');
$dotenv->load();

use HelpScoutApp\DynamicApp;
use Illuminate\Database\Eloquent\Model as Model;
use Carbon\Carbon;

setlocale(LC_MONETARY, 'en_US.UTF-8');

$app = new DynamicApp(getenv('HS_SECRET'));
if ($app->isSignatureValid())
{
  $customer = $app->getCustomer();

  $settings = array(
    'driver'    => getenv('DB_TYPE'),
    'host'      => getenv('DB_HOST'),
    'port'      => '3306',
    'database'  => getenv('DB_DATABASE'),
    'username'  => getenv('DB_USERNAME'),
    'password'  => getenv('DB_PASSWORD'),
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
  );

  $hostname = getenv('APP_URL');

  $connFactory = new \Illuminate\Database\Connectors\ConnectionFactory(new Illuminate\Container\Container);
  $conn = $connFactory->make($settings);
  $resolver = new \Illuminate\Database\ConnectionResolver();
  $resolver->addConnection('default', $conn);
  $resolver->setDefaultConnection('default');
  \Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver);

  class Invoice extends Model {

      public $timestamps = false;

      public function invoice_status()
    {
        return $this->belongsTo('invoice_status');
    }

    protected $dates = ['created_at'];
  }

  class Invoice_status extends Model {

      public $timestamps = false;
  }

  class Contact extends Model {

    public $timestamps = false;
  }

  $contacts = Contact::whereIn('email', $customer->getEmails())->pluck('client_id');

  $invoices = Invoice::whereIn('client_id', $contacts)->with('invoice_status')->latest()->take(10)->get();

  $statuscolor = collect([
    ['id' => '1', 'color' => 'cancelled'],
    ['id' => '2', 'color' => 'cancelled'],
    ['id' => '3', 'color' => 'open'],
    ['id' => '4', 'color' => 'open'],
    ['id' => '5', 'color' => 'completed'],
    ['id' => '6', 'color' => 'completed'],
  ]);

  $html = array();
  #$html[] = '<h4>Invoices</h4>';

  foreach($invoices as $invoice)
    {
      $color = $statuscolor->where('id', $invoice->invoice_status->id)->first();
      $html[] = '<table class="table-condensed ecomm-app" style="width:100%;">';
      $html[] = '<tbody>';
      $html[] = '<tr>';
      $html[] = '<td class="num"><a href="'.$hostname.'/invoices/'.$invoice->id.'/edit" target="_blank">'.$invoice->invoice_number.'</a></td>';
      $html[] = '<td style="text-align:right;">'.money_format('%.2n', $invoice->amount).'</td>';
      $html[] = '</tr>';
      $html[] = '<tr class="order-info">';
      $html[] = '<td class="muted">'.$invoice->created_at->toFormattedDateString().'</td>';
      $html[] = '<td class="'.$color['color'].'" style="text-align:right;">'.$invoice->invoice_status->name.'</td>';
      $html[] = '</tr>';
      $html[] = '</tbody>';
      $html[] = '</table>';

    }
  echo $app->getResponse($html);
}
else
{
  echo 'Invalid Request';
}
