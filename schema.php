<?php
    include("config.php");
    
    $conn->query("drop table if exists cart") or die("something went wrong while dropping table users");
    $conn->query("drop table if exists order_product") or die("something went wrong while dropping table users");
    $conn->query("drop table if exists products") or die("Something went wrong while dropping table products");
    $conn->query("drop table if exists orders") or die("something went wrong while dropping table users");
    $conn->query("drop table if exists users") or die("something went wrong while dropping table users");
    $conn->query("drop table if exists product_name_price") or die("something went wrong while dropping table users");
    $conn->query("CREATE TABLE if not exists product_name_price (
        id int primary key auto_increment,
        name varchar(50) not null,
        price FLOAT not null
    );");

    $sql = "CREATE TABLE if not exists products (
            id int primary key auto_increment,
            pnp int(11) not null,
            brand varchar(50) not null, 
            quantity int default 1,
            image varchar(50),
            createdDate DATETIME DEFAULT CURRENT_TIMESTAMP ,
            foreign key(pnp) references product_name_price(id)
        )";

    $conn->query($sql) or die("table products creation failed");
    $datetime = new DateTime("now",new DateTimeZone("Asia/Kathmandu"));
    $date = $datetime->format("Y-m-d H:i:s");
    $conn->query("insert into product_name_price(name,price) values('asdfasdfsda',432.12)");
    $id = mysqli_insert_id($conn);
    $conn->query("insert into products(pnp,image,brand,quantity,createdDate) values($id,'http://localhost/img/grafitti2.jpg','bmw',12,'$date')") or die("inserting value failed in table products");

    $conn->query("CREATE TABLe if not exists users (
            id int primary key auto_increment,
            firstName varchar(50) not null,
            lastName varchar(50) not null,
            username varchar(50) not null,
            password varchar(255) not null,
            phoneNumber varchar(10) not null,
            email varchar(50) not null,
            address varchar(50) not null,
            role varchar(50) not null
        )") or die("table users creation failed");

    $password = md5("admin");
    $conn->query("insert into users(firstName,lastName,username,password,phoneNumber,email,address,role) values('adsf','adsfsdf','admin','$password','981233123','asdfdsf@gmail.com','asdfsd123','admin')");
    $conn->query("CREATE table if not exists cart(
        id int primary key auto_increment,
        bid int(11) not null,
        uid int(11) not null,
        quantity int default 1,
        foreign key (bid) references product_name_price(id),
        foreign key (uid) references users(id))
        ");
    $conn->query("CREATE TABLE IF NOT EXISTS orders ( 
            id int primary key auto_increment,
            uid int(11) not null,
            total_price FLOAT(50) not null,
            status varchar(50) default 'pending',
            payment_status varchar(50) default 'unpaid',
            payment_method varchar(50) default NULL,
            createdDate DATETIME DEFAULT CURRENT_TIMESTAMP,
            foreign key (uid) references users(id))
        ");

    $conn->query("CREATE TABLE IF NOT EXISTS order_product(
            id int primary key auto_increment,
            oid int(11) not null,
            bid int(11) not null,
            quantity int(11) not null,
            
            foreign key (oid) references orders(id),
            foreign key (bid) references product_name_price(id)
        )");
    echo "<p style='font-size:50px;color:green;text-align:center;'>Success</p>"
?>