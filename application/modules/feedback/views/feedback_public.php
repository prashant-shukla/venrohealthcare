<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Feedback - VENRO HEALTH CARE</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background:#f4f6f9; margin:0; padding:0; color:#333; }
        .card { max-width:520px; margin:60px auto; background:#fff; border-radius:6px;
                box-shadow:0 2px 10px rgba(0,0,0,.08); padding:30px; }
        h2 { margin-top:0; color:#2a3f54; }
        label { display:block; margin:14px 0 5px; font-weight:bold; }
        input[type=text], textarea, select { width:100%; padding:9px; border:1px solid #ccc;
                border-radius:4px; box-sizing:border-box; font-size:14px; }
        textarea { min-height:90px; }
        .btn { margin-top:18px; background:#26a65b; color:#fff; border:none; padding:11px 22px;
               border-radius:4px; font-size:15px; cursor:pointer; }
        .muted { color:#888; font-size:13px; }
        .brand { text-align:center; font-weight:bold; color:#2a3f54; letter-spacing:1px; margin-bottom:10px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">VENRO HEALTH CARE</div>

        <?php if (!empty($error)) { ?>
            <h2>Feedback</h2>
            <p><?php echo $error; ?></p>

        <?php } elseif (!empty($done)) { ?>
            <h2>Already Submitted</h2>
            <p>This feedback has already been submitted. Thank you.</p>

        <?php } elseif (!empty($thanks)) { ?>
            <h2>Thank You!</h2>
            <p>Your feedback has been submitted successfully.</p>

        <?php } elseif (!empty($row)) { ?>
            <h2><?php echo $row->feedback_type == 'Business' ? 'Service Feedback' : 'Nurse Feedback'; ?></h2>
            <p class="muted">
                <?php if ($row->feedback_type == 'Nurse' && !empty($row->nurse_name)) { ?>
                    Regarding nurse: <strong><?php echo htmlspecialchars($row->nurse_name); ?></strong>
                <?php } else { ?>
                    We'd love to hear about your experience with our bedside nursing service.
                <?php } ?>
            </p>

            <form method="post" action="<?php echo base_url('feedback/submit'); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($row->token); ?>">

                <label>Your Name</label>
                <input type="text" name="customer_name" value="">

                <label>Rating</label>
                <select name="rating">
                    <option value="">-- Select --</option>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Good</option>
                    <option value="3">3 - Average</option>
                    <option value="2">2 - Poor</option>
                    <option value="1">1 - Very Poor</option>
                </select>

                <label>Comments</label>
                <textarea name="comments" required placeholder="Your feedback"></textarea>

                <button type="submit" class="btn">Submit Feedback</button>
            </form>
        <?php } ?>
    </div>
</body>
</html>
