<section id="main-content">
    <section class="wrapper">

        <section class="panel">

            <header class="panel-heading">

                <?php echo !empty($records) ? $records[0]->nurse_name : 'Nurse'; ?>

                Payment Details

            </header>

            <div class="panel-body">

                <?php
                // Default Values
                $grand_total = 0;
                $paid_total = 0;
                $remaining = 0;
                ?>

                <!-- PAYMENT ADD FORM -->

                <div class="row">

                    <div class="col-md-12">

                        <form method="post"
                            action="<?php echo base_url('nurse/addPayment'); ?>">

                            <input type="hidden"
                                name="nurse_id"
                                value="<?php echo !empty($records) ? $records[0]->nurse_id : ''; ?>">

                            <div class="row">

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Amount</label>

                                        <input type="number"
                                            name="amount"
                                            class="form-control"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Payment Date</label>

                                        <input type="date"
                                            name="payment_date"
                                            class="form-control"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Note</label>

                                        <input type="text"
                                            name="note"
                                            class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-2">

                                    <label>&nbsp;</label>

                                    <button type="submit"
                                        class="btn btn-success btn-block">
                                        Add Payment
                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>

                <hr>


                <!-- BILLING TABLE -->

                <h4><b>Total Billing</b></h4>

                <table class="table table-bordered">

                    <thead>

                        <tr>
                            <th>Patient</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Days</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Total</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if (!empty($records)) { ?>

                            <?php foreach ($records as $row) {

                                $start = strtotime($row->start_date);
                                $end = strtotime($row->end_date);

                                $days = ($end - $start) / (60 * 60 * 24) + 1;

                                // Billing Calculate
                                if ($row->billing_type == 'Day') {

                                    $total = $days * $row->billing_amount;
                                } elseif ($row->billing_type == 'Week') {

                                    $total = ceil($days / 7) * $row->billing_amount;
                                } else {

                                    $total = ceil($days / 30) * $row->billing_amount;
                                }

                                // Transport Charge Add
                                $total += !empty($row->transport_charge)
                                    ? $row->transport_charge
                                    : 0;

                                // Grand Total
                                $grand_total += $total;
                            ?>

                                <tr>


                                    <td><?php echo $row->patient_name; ?></td>

                                    <td><?php echo $row->start_date; ?></td>

                                    <td><?php echo $row->end_date; ?></td>

                                    <td><?php echo $days; ?></td>

                                    <td><?php echo $row->billing_type; ?></td>

                                    <td><?php echo $row->billing_amount; ?></td>

                                    <td>
                                        <b>
                                            ₹<?php echo number_format($total, 2); ?>
                                        </b>
                                    </td>

                                </tr>

                            <?php } ?>

                        <?php } else { ?>

                            <tr>
                                <td colspan="8" class="text-center">
                                    No Billing Found
                                </td>
                            </tr>

                        <?php } ?>

                    </tbody>

                </table>


                <!-- PAYMENT HISTORY -->

                <h4><b>Payment History</b></h4>

                <table class="table table-bordered">

                    <thead>

                        <tr>

                            <th>Date</th>
                            <th>Amount</th>
                            <th>Note</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if (!empty($payments)) { ?>

                            <?php foreach ($payments as $pay) {

                                $paid_total += $pay->amount;
                            ?>

                                <tr>

                                    <td><?php echo $pay->payment_date; ?></td>

                                    <td>
                                        ₹<?php echo number_format($pay->amount, 2); ?>
                                    </td>

                                    <td><?php echo $pay->note; ?></td>

                                </tr>

                            <?php } ?>

                        <?php } else { ?>

                            <tr>
                                <td colspan="3" class="text-center">
                                    No Payment Found
                                </td>
                            </tr>

                        <?php } ?>

                    </tbody>

                </table>


                <?php
                // Remaining Amount
                $remaining = $grand_total - $paid_total;
                ?>


                <!-- SUMMARY -->

                <div class="row">

                    <div class="col-md-4">

                        <div class="alert alert-info">

                            <h4>
                                Total Billing:
                                ₹<?php echo number_format($grand_total, 2); ?>
                            </h4>

                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="alert alert-success">

                            <h4>
                                Total Paid:
                                ₹<?php echo number_format($paid_total, 2); ?>
                            </h4>

                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="alert alert-danger">

                            <h4>
                                Remaining:
                                ₹<?php echo number_format($remaining, 2); ?>
                            </h4>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </section>
</section>