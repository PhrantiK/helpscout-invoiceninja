<?php

require 'vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../../');
$dotenv->load();

use HelpScoutApp\DynamicApp;
use Illuminate\Database\Eloquent\Model as Model;
use Carbon\Carbon;

setlocale(LC_MONETARY, 'en_US.UTF-8');

$app = new DynamicApp(getenv('HS_SECRET2'));
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

  class Client extends Model {

    public $timestamps = false;

  }

  class Contact extends Model {

    public function client()
    {
        return $this->belongsTo('client');
    }

    public $timestamps = false;

  }

  class Invoice extends Model {

      public function invoice_status()
    {
        return $this->belongsTo('invoice_status');
    }

    public $timestamps = false;

  }

  class Invoice_status extends Model {

      public $timestamps = false;

  }

  $html = array();

  $contacts = Contact::whereIn('email', $customer->getEmails())->with('client')->get();

  if ($contacts->isEmpty()) 
  {
    /*
    $html[] = '<table class="table-condensed ecomm-app" style="width:100%;">';
    $html[] = '<tbody>';
    $html[] = '<tr>';
    $html[] = '<td><a href="https://google.com" class="btn">Create Client</a></td>';
    $html[] = '</tr>';
    $html[] = '</tbody>';
    $html[] = '</table>';
    */
    $html[] = '<h4>No Invoices</h4>';
    echo $app->getResponse($html);
  }
  else 
  {

  $latestcontact = $contacts->sortByDesc('created_at')->first();

  $clid = $contacts->pluck('client_id');

  $invoices = Invoice::whereIn('client_id', $clid)->where('is_deleted', '=', 0)->with('invoice_status')->get();

  $invoices = $invoices->sortByDesc('invoice_date')->take(10);

  $statuscolor = collect([
    ['id' => '1', 'color' => 'cancelled'],
    ['id' => '2', 'color' => 'cancelled'],
    ['id' => '3', 'color' => 'open'],
    ['id' => '4', 'color' => 'open'],
    ['id' => '5', 'color' => 'completed'],
    ['id' => '6', 'color' => 'completed'],
  ]);

  $html[] = '<table class="table-condensed ecomm-app" style="width:100%;">';
  $html[] = '<tbody>';
  $html[] = '<tr>';
  $html[] = '<td><a href="'.$hostname.'/clients/'.$latestcontact->client->public_id.'" class="btn">Client Portal</a></td>';
  $html[] = '<td style="text-align:right"><a href="'.$hostname.'/invoices/create/'.$latestcontact->client->public_id.'" class="btn">New Invoice</a></td>';
  $html[] = '</tr>';
  $html[] = '</tbody>';
  $html[] = '</table>';

  foreach($invoices as $invoice)
    {
      $date = new DateTime($invoice->invoice_date);
      $color = $statuscolor->where('id', $invoice->invoice_status->id)->first();
      $html[] = '<table class="table-condensed ecomm-app" style="width:100%;">';
      $html[] = '<tbody>';
      $html[] = '<tr>';
      $html[] = '<td class="num"><a href="'.$hostname.'/invoices/'.$invoice->public_id.'/edit" target="_blank">'.$invoice->invoice_number.'</a></td>';
      $html[] = '<td style="text-align:right;">'.money_format('%.2n', $invoice->amount).'</td>';
      $html[] = '</tr>';
      $html[] = '<tr class="order-info">';
      $html[] = '<td class="muted">'.$date->format('jS M, Y').'</td>';
      $html[] = '<td class="'.$color['color'].'" style="text-align:right;">'.$invoice->invoice_status->name.'</td>';
      $html[] = '</tr>';
      $html[] = '</tbody>';
      $html[] = '</table>';

    }
  echo $app->getResponse($html);
  }
}
else
{
  echo 'Invalid Request';
}
