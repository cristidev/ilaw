<div class="row">
    <div class="col-xs-12 col-md-6 col-md-offset-3">
        <div class="form-box">
            <div class="row">
                <h2 class="page-header">Login first</h2>
            </div>
            <fieldset>
                <form class="form-horizontal" role="form" method="POST" id="login_user" >
                    <div class="row">
                        <div class="col-xs-12 col-md-12">
                            <div class="form-group clearfix">
                                <label>Username *:</label>
                                <input type="text" class="form-control" name="username" id="username" placeholder="me@company.com" value="" required="required">
                                <div class="alert alert-danger hide" id="valid_email"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-12">
                            <div class="form-group clearfix">
                                <label>Password *:</label>
                                <input type="password" class="form-control" placeholder="" name="password" id="password" required="required">
                                <div class="alert alert-danger hide" id="valid_password"></div>
                            </div>
                        </div>
                    </div>
                    <div class="footer">
                        <button type="submit" class="btn btn-primary btn-block">Sign me in</button>
                    </div>
                </form>
            </fieldset>
        </div>
    </div>
</div>