<?php

class User extends View
{
  private $db;

  public function __construct()
  {
    $this->db = new Database;
  }

  public function account()
  {
    $user = $_SESSION['id'];
    $purchases = $this->db->get("SELECT product.figure, product.name, product.price, size.name AS size, stock.quantity, stock.id FROM product, size, stock, purchase WHERE product.id = stock.id_product AND size.id = stock.id_size AND stock.id = purchase.id_stock AND purchase.pay = 0 AND purchase.id_user = $user");
    $orders = $this->db->get("SELECT product.figure, product.name, product.price, size.name AS size FROM product, size, stock, purchase WHERE product.id = stock.id_product AND size.id = stock.id_size AND stock.id = purchase.id_stock AND purchase.pay = 1 AND purchase.id_user = $user");

    \Stripe\Stripe::setApiKey('sk_test_51GvfkfLSldr7Tk8n5kVFWHIaEYq1mkFQF09yhVVhv3MoHU3gjOFmLYVhrxJUxQMWy9kIfTM2k3kEmau4L50f0lwR00l8DDTTLX');

    if ($purchases)
    {
      $montant = 0; foreach ($purchases as $purchase) $montant = $montant + $purchase->price;

      $stripe = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
          'price_data' => [
            'currency' => 'eur',
            'product_data' => [
              'name' => 'Montant de votre panier :',
            ],
            'unit_amount' => $montant * 100,
          ],
          'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://test.lan/pay',
        'cancel_url' => 'http://test.lan/account'
      ]);

      $stripe->panier = $montant;
    }

    $this->render('account', [
      'purchases' => $purchases,
      'orders' => $orders,
      'stripe' => $stripe ?? 0
    ]);
  }

  public function pay()
  {
    $products = $this->db->search('purchase', ['id_user' => $_SESSION['id'], 'pay' => 0]);
    foreach ($products as $product) $this->db->set("UPDATE stock SET quantity = quantity - 1 WHERE id_product = $product->id");
    $this->db->set('UPDATE purchase SET pay = 1 WHERE id_user = ' . $_SESSION['id']);
    $_SESSION['purchase'] = 0;
    location('/account');
  }

  public function remove($id)
  {
    $purchase = $this->db->search('purchase', ['id_stock' => $id, 'id_user' => $_SESSION['id']]);
    $this->db->throw('purchase', $purchase->id);
    $_SESSION['purchase']--;
    location('/account');
  }

  public function login_get()
  {
    $this->render('login');
  }

  public function login_post()
  {
    $email = secure($_POST['email']);
    $password = secure($_POST['password']);

    if ($user = $this->db->search('user', ['email' => $email, 'password' => $password]))
    {
      $_SESSION['logged'] = true;
      $_SESSION['id'] = $user->id;
      $_SESSION['name'] = $user->name;
      $_SESSION['purchase'] = $this->db->get("SELECT COUNT(id) as orders FROM purchase WHERE pay = 0 AND id_user = $user->id", true)->orders;
      location('/');
    }
    else
    {
      $this->render('login', [
        'email' => $email,
        'password' => $password
      ]);
    }
  }

  public function register_get()
  {
    $this->render('register');
  }

  public function register_post()
  {
    $name = secure($_POST['name']);
    $email = secure($_POST['email']);
    $password = secure($_POST['password']);

    if (!$this->db->search('user', ['email' => $email]))
    {
      $new = [null, $name, $email, $password];
      $this->db->add('user', $new);

      $_SESSION['logged'] = true;
      $_SESSION['id'] = $this->db->newId();
      $_SESSION['name'] = $name;
      $_SESSION['purchase'] = 0;
      location('/');
    }
    else
    {
      $this->render('register', [
        'name' => $name,
        'email' => $email,
        'password' => $password
      ]);
    }
  }

  public function logout()
  {
    stopSession();
    location('/');
  }
}