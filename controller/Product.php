<?php

class Product extends View
{
  private $db;

  public function __construct()
  {
    $this->db = new Database;
  }

  public function list()
  {
    $products = $this->db->get('SELECT * FROM product ORDER BY RAND()');

    $this->render('list', [
      'products' => $products
    ]);
  }

  public function show($id)
  {
    if (!$product = $this->db->find('product', $id)) location('/');
    $product->stocks = $this->db->get("SELECT size.name, stock.quantity FROM size, stock WHERE size.id = stock.id_size AND stock.id_product = $id");

    $this->render('show', [
      'product' => $product
    ]);
  }

  public function add($id)
  {
    $size = $this->db->search('size', ['name' => $_POST['size']]);
    $stock = $this->db->search('stock', ['id_product' => $id, 'id_size' => $size->id]);
    $new = [null, $stock->id, $_SESSION['id'], 0];
    $this->db->add('purchase', $new);
    $_SESSION['purchase']++;
    location('/');
  }
}