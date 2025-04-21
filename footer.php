
<style>
    .custom-footer {
            background-color: #ffaec8;
            padding: 15px 20px 30px 20px;
            font-family: 'Segoe UI', sans-serif;
            font-size: 14px;
            position: relative;
        }

        .footer-content {
            display: flex;
            justify-content: center;
            /* Center all columns */
            align-items: flex-start;
            gap: 50px;
            /* Optional: spacing between columns */
            flex-wrap: wrap;
            text-align: center;
            /* Center text inside each column */
        }

        .footer-column {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            /* Align items to the left */
            justify-content: flex-start;
            min-width: 170px;
        }

        .footer-column h4 {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .footer-column img {
            width: 80px;
            height: auto;
            margin-top: 5px;
        }

        .follow-us-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .follow-us i {
            font-size: 20px;
        }

        .follow-us span {
            font-size: 14px;
        }


        .follow-us img {
            width: 20px;
            height: 20px;
            vertical-align: middle;
            margin-right: 5px;
        }

        .follow-us span {
            font-size: 14px;
            vertical-align: middle;
            display: inline-block;
            margin-top: 5px;
        }

        .footer-bottom {
            width: 100%;
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #333;
            position: static;
        }

        .contact-info {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .contact-info .address,
        .contact-info .email-phone {
            flex: 1;
            min-width: 200px;
        }
</style>
<footer class="custom-footer">
    <div class="footer-content">
        <div class="footer-column contact-us">
            <h5>Contact Us:</h5>
            <div class="contact-info">
                <div class="address">
                    <p>016 Cong Ding Tanjuatco St. Brgy. San Isidro Tanay, Rizal</p>
                </div>
                <div class="email-phone">
                    <p>Email: kmtanay15@gmail.com<br>Phone: (+63)905-097-0672</p>
                </div>
            </div>
        </div>

        <div class="footer-column">
            <h5>Payment</h5>
            <img src="icons/gcash.png" alt="GCash Logo">
        </div>

        <div class="footer-column follow-us">
            <h5>Follow Us</h5>
            <div class="follow-us-content">
                <i class="fa-brands fa-facebook"></i>
                <a href="https://www.facebook.com/km.tanay" target="_blank">
                    <span>KM Tanay</span>

                </a>

            </div>

        </div>

    </div>
    <div class="footer-bottom">
        <p>© KM Tanay 2024. All Rights Reserved.</p>
    </div>
</footer>