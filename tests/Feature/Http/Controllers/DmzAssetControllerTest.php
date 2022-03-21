<?php

use App\Models\User;

test('DmzAsset is not handled by php engine', function () {

    $this->be(User::factory()->create());
    $file='image.php';
    $path= storage_path('dmz-assets/'.$file);
    $myfile = fopen($path, "w") or die("Unable to open file!");
    $txt = "<?php \necho 'not hacked';";
    fwrite($myfile, $txt);
    fclose($myfile);

    /** @var $response \Symfony\Component\HttpFoundation\BinaryFileResponse */
    $response = $this->get('/dmz-assets/'.$file);
    $content = $response->getFile()->getContent();

    unlink($path);

    $response->assertStatus(200);
    $this->assertEquals($txt,$content);

});

test('Non existing dmz-asset returns 404', function () {

    $this->be(User::factory()->create());

    /** @var $response \Symfony\Component\HttpFoundation\BinaryFileResponse */
    $response = $this->get('/dmz-assets/404');

    $response->assertStatus(404);


});


test('DmzAsset is protected by auth', function () {

    $response = $this->get('/dmz-assets/image.php');

    $response->assertRedirect('/login');
});
