<?php

use App\Models\User;

test('DmzAsset is not handled by php engine', function () {

    $this->be(User::factory()->create());
    $file = 'image.php';
    $path = attachmentPathInUploadDisk($file);
    $txt = "<?php \necho 'not hacked';";

    uploadDisk()->put($path, $txt);

    /** @var $response \Symfony\Component\HttpFoundation\BinaryFileResponse */
    $response = $this->get(route('dmz-asset', ['file' => $path]));

    $response->assertStatus(200);
    $content = $response->getFile()->getContent();
    $this->assertEquals($txt, $content);

    //cleanup
    uploadDisk()->delete($path);

});

test('Non existing dmz-asset returns 404', function () {

    $this->be(User::factory()->create());

    /** @var $response \Symfony\Component\HttpFoundation\BinaryFileResponse */
    $response = $this->get(route('dmz-asset', ['file' => '404']));

    $response->assertStatus(404);

});

test('DmzAsset is protected by auth', function () {

    $response = $this->get(route('dmz-asset', ['file' => 'image.php']));

    $response->assertRedirect('/login');
});
