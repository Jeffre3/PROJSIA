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

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: register.php");
    exit();
}

// Handle form submission to add a product
if (isset($_POST['add'])) {
    $title = $_POST['title'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];

        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'uploads/';
            $file_name = uniqid('product_', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                $sql = "INSERT INTO products (title, price, description, image) VALUES ('$title', '$price', '$description', '$file_path')";
                if ($conn->query($sql) === TRUE) {
                    echo "<div class='alert alert-success'>New product added successfully</div>";
                } else {
                    echo "<div class='alert alert-danger'>Error: " . $sql . "<br>" . $conn->error . "</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Error uploading the image.</div>";
            }
        } else {
            echo "<div class='alert alert-warning'>Invalid image format. Only JPG, PNG, and GIF are allowed.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>No image uploaded or there was an error.</div>";
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM products WHERE id = $delete_id";
    if ($conn->query($delete_sql) === TRUE) {
        echo "<div class='alert alert-success'>Product deleted successfully</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// Handle edit request (update)
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    $file_path = $_POST['current_image']; // Keep the current image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'uploads/';
            $file_name = uniqid('product_', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_path = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                echo "<div class='alert alert-success'>Image uploaded successfully</div>";
            } else {
                echo "<div class='alert alert-danger'>Error uploading image</div>";
            }
        }
    }

    $update_sql = "UPDATE products SET title = '$title', price = '$price', description = '$description', image = '$file_path' WHERE id = $id";
    if ($conn->query($update_sql) === TRUE) {
        echo "<div class='alert alert-success'>Product updated successfully</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// Fetch products to display for editing or deletion
$sql = "SELECT * FROM products";
$query = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product List</title>
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<style>
  /* Consistent button styling */
.highlight-btn {
    background-color: #1a2a6c;  /* Consistent color (dark blue) */
    color: white;
    font-weight: bold;
    border: 2px solid #1a2a6c;  /* Darker border for emphasis */
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.highlight-btn:hover {
    background-color: #2a3d72;  /* Darken the color on hover */
    transform: scale(1.05);  /* Slightly enlarge the button when hovered */
}

/* Uniform image size */
.table img {
    width:500px;  /* Fixed width */
    height: 150x; /* Fixed height */
    object-fit: cover; /* Maintain aspect ratio, filling the dimensions */
} 
</style>
<body>

  <div class="container mt-4">
    <div class="content-wrapper">
    <a href="index.php" class="btn btn-primary mt-2 ml-1 highlight-btn">Back</a>
      <section class="content-header text-center mb-4">
        <h2 class="display-4">Website List</h2>
      </section>

      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <button class="btn btn-primary mt-2 ml-1 highlight-btn" data-toggle="modal" data-target="#addweb">
              
                <i class="fa fa-plus"></i> Add New Product
              </button>
            </div>
            <div class="card-body">
              <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                  <tr>
                    <th>Title</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  while ($row = $query->fetch_assoc()) {
                      echo "<tr>
                              <td>" . $row['title'] . "</td>
                              <td>$" . $row['price'] . "</td>
                              <td>" . $row['description'] . "</td>
                              <td><img src='" . $row['image'] . "' alt='" . $row['title'] . "' width='100'></td>
                              <td>
                                  <a href='edit.php?id=" . $row['id'] . "' class='btn btn-info btn-sm'><i class='fa fa-edit'></i> Edit</a>
                                  <a href='?delete_id=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")'><i class='fa fa-trash'></i> Delete</a>
                              </td>
                            </tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Adding Product -->
  <div class="modal fade" id="addweb">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><b>Add New Product</b></h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
              <label for="title">Title</label>
              <input type="text" class="form-control" name="title" required>
            </div>
            <div class="form-group">
              <label for="price">Price</label>
              <input type="text" class="form-control" name="price" required>
            </div>
            <div class="form-group">
              <label for="description">Description</label>
              <input type="text" class="form-control" name="description" required>
            </div>
            <div class="form-group">
              <label for="image">Image</label>
              <input type="file" class="form-control" name="image" required>
            </div>
            <button type="submit" class="btn btn-primary" name="add">Add Product</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Include jQuery and Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
