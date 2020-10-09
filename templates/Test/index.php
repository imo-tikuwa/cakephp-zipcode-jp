<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>ZipcodeJp Test</title>
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script type="text/javascript">
            $(function(){
                $("#zipcode").on("keyup", function(){
                    $("#pref").text('');
                    $("#city").text('');
                    $("#address").text('');
                    if ($(this).val().length == 7) {
                        let requesturl = '/zipcode-jp/' + $(this).val() + '.json';
                        $.ajax({
                            type: 'get',
                            url: requesturl,
                            dataType: 'json'
                        }).done(function(result) {
                            if (result != null) {
                                $("#pref").text(result['pref']);
                                $("#city").text(result['city']);
                                $("#address").text(result['address']);
                            }
                        });
                    }
                });
            });
        </script>
    </head>
    <body>
        <table>
            <tr>
                <th>郵便番号</th>
                <td><input type="text" id="zipcode" maxlength="7" placeholder="ここに郵便番号を入力" /></td>
            </tr>
            <tr>
                <th>都道府県名</th>
                <td id="pref"></td>
            </tr>
            <tr>
                <th>市区町村名</th>
                <td id="city"></td>
            </tr>
            <tr>
                <th>町域名</th>
                <td id="address"></td>
            </tr>
        </table>
    </body>
</html>