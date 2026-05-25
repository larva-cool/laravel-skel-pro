<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Observers;

use App\Facades\Upload;
use App\Models\System\Attachment;

/**
 * 附件模型观察者
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class AttachmentObserver
{
    /**
     * Handle the Attachment "created" event.
     */
    public function created(Attachment $attachment): void {}

    /**
     * Handle the Attachment "updated" event.
     */
    public function updated(Attachment $attachment): void
    {
        //
    }

    /**
     * Handle the Attachment "deleted" event.
     */
    public function deleted(Attachment $attachment): void
    {
        Upload::destroy($attachment->file_path);
    }
}
