<?php
function sendOrderEmails($orderDetails) {
    // Customer email
    $customerSubject = "The Protein Bakery - Order Confirmation #".$orderDetails['order_id'];
    $customerMessage = "
        <html>
        <head>
            <title>Your Order Confirmation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .order-table { width: 100%; border-collapse: collapse; }
                .order-table th, .order-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .order-table th { background-color: #f2f2f2; }
                .footer { margin-top: 20px; font-size: 0.9em; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>The Protein Bakery</h2>
                <p>Order Confirmation #".$orderDetails['order_id']."</p>
            </div>
            <div class='content'>
                <p>Hello ".htmlspecialchars($orderDetails['customer_name']).",</p>
                <p>Thank you for your order! We're preparing your homemade protein bars with care.</p>
                
                <h3>Order Summary</h3>
                <table class='order-table'>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>";
    
    foreach($orderDetails['items'] as $item) {
        $customerMessage .= "
                    <tr>
                        <td>".htmlspecialchars($item['name'])."</td>
                        <td>".$item['quantity']."</td>
                        <td>HKD ".number_format($item['price'], 2)."</td>
                        <td>HKD ".number_format($item['price'] * $item['quantity'], 2)."</td>
                    </tr>";
    }
    
    $customerMessage .= "
                    <tr>
                        <td colspan='3'><strong>Total</strong></td>
                        <td><strong>HKD ".number_format($orderDetails['total'], 2)."</strong></td>
                    </tr>
                </table>
                
                <h3>Delivery Information</h3>
                <p>".nl2br(htmlspecialchars($orderDetails['address']))."</p>
                
                <div class='footer'>
                    <p>We'll contact you if we have any questions about your order.</p>
                    <p>Thank you for choosing The Protein Bakery!</p>
                </div>
            </div>
        </body>
        </html>
    ";
    
    // Owner email (sent to you)
    $ownerSubject = "New Order #".$orderDetails['order_id']." - ".$orderDetails['customer_name'];
    $ownerMessage = "
        <html>
        <head>
            <title>New Order Notification</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .order-table { width: 100%; border-collapse: collapse; }
                .order-table th, .order-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .order-table th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <h2>New Order Received</h2>
            <p><strong>Order #".$orderDetails['order_id']."</strong></p>
            
            <h3>Customer Details</h3>
            <p><strong>Name:</strong> ".htmlspecialchars($orderDetails['customer_name'])."</p>
            <p><strong>Email:</strong> ".htmlspecialchars($orderDetails['email'])."</p>
            <p><strong>Phone:</strong> ".htmlspecialchars($orderDetails['phone'])."</p>
            <p><strong>Address:</strong><br>".nl2br(htmlspecialchars($orderDetails['address']))."</p>
            
            <h3>Order Items</h3>
            <table class='order-table'>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>";
    
    foreach($orderDetails['items'] as $item) {
        $ownerMessage .= "
                <tr>
                    <td>".htmlspecialchars($item['name'])."</td>
                    <td>".$item['quantity']."</td>
                    <td>HKD ".number_format($item['price'], 2)."</td>
                    <td>HKD ".number_format($item['price'] * $item['quantity'], 2)."</td>
                </tr>";
    }
    
    $ownerMessage .= "
                <tr>
                    <td colspan='3'><strong>Total</strong></td>
                    <td><strong>HKD ".number_format($orderDetails['total'], 2)."</strong></td>
                </tr>
            </table>
        </body>
        </html>
    ";
    
    // Send emails
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: The Protein Bakery <orders@theproteinbakery.com>\r\n";
    
    // Send to customer
    mail($orderDetails['email'], $customerSubject, $customerMessage, $headers);
    
    // Send to you (the owner)
    mail('the-protein-bakery@outlook.com', $ownerSubject, $ownerMessage, $headers);
}