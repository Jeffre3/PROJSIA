<?php
// Start session to check if the user is logged in
session_start();

// Database Configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "webforgedb";

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle the product selection after searching
if (isset($_POST['search'])) {
    $search_query = $_POST['search_query'];
    $sql = "SELECT * FROM products WHERE title LIKE '%$search_query%'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    } else {
        $error_message = "No products found matching your search.";
    }
}

// If a product is selected, fetch its details
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Product not found!";
        exit();
    }
}

// Handle form submission to update product details
if (isset($_POST['update'])) {
    $title = $_POST['title'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $file_path = $_POST['current_image']; // Keep the current image by default

    // Check if a new image is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'uploads/';
            $file_name = uniqid('product_', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_path = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                echo "Image uploaded successfully";
            } else {
                echo "Error uploading image";
            }
        }
    }

    // Update the product in the database
    $update_sql = "UPDATE products SET title = '$title', price = '$price', description = '$description', image = '$file_path' WHERE id = $id";
    if ($conn->query($update_sql) === TRUE) {
        echo "Product updated successfully";
        header("Location: try.php"); // Redirect back to the admin page
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Product</title>
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="container mt-4">
    <h2>Edit Product</h2>

    <?php if (isset($products)): ?>
      <h3>Search Results</h3>
      <ul class="list-group mt-2">
        <?php foreach ($products as $prod): ?>
          <li class="list-group-item">
            <a href="edit.php?id=<?php echo $prod['id']; ?>"><?php echo $prod['title']; ?></a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
      <div class="alert alert-warning mt-3">
        <?php echo $error_message; ?>
      </div>
    <?php endif; ?>

    <?php if (isset($product)): ?>
      <form method="POST" action="edit.php?id=<?php echo $product['id']; ?>" enctype="multipart/form-data">
        <div class="form-group">
          <label for="title">Title</label>
          <input type="text" class="form-control" name="title" value="<?php echo $product['title']; ?>" required>
        </div>
        <div class="form-group">
          <label for="price">Price</label>
          <input type="text" class="form-control" name="price" value="<?php echo $product['price']; ?>" required>
        </div>
        <div class="form-group">
          <label for="description">Description</label>
          <input type="text" class="form-control" name="description" value="<?php echo $product['description']; ?>" required>
        </div>
        <div class="form-group">
          <label for="image">Current Image</label><br>
          <img src="<?php echo $product['image']; ?>" alt="Product Image" width="150"><br><br>
          <label for="image">Upload New Image (optional)</label>
          <input type="file" class="form-control" name="image">
          <input type="hidden" name="current_image" value="<?php echo $product['image']; ?>"> <!-- Keep the current image path -->
        </div>
        <button type="submit" class="btn btn-primary" name="update">Update Product</button>
      </form>
    <?php endif; ?>

  </div>

  <!-- Include jQuery and Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
