<?php

namespace RefinedDigital\CMS\Modules\Core\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Notification extends Mailable
{
    use Queueable, SerializesModels;

    protected $settings;
    public $body;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->body = $settings->body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (isset($this->settings, $this->settings->send_as_plain_text) && $this->settings->send_as_plain_text) {
          $data = $this->text('core::emails.notification-plain-text');
        } else {
          $data = $this->markdown('core::emails.notification');
        }

        $data->subject($this->settings->subject);

        if(isset($this->settings->cc)) {
            $data->cc(help()->explodeAndTrim($this->settings->cc));
        }
        if(isset($this->settings->bcc)) {
            $data->bcc(help()->explodeAndTrim($this->settings->bcc));
        }
        if(isset($this->settings->reply_to)) {
            $data->replyTo($this->settings->reply_to);
        }

        if (isset($this->settings->files) && is_array($this->settings->files)) {
            foreach ($this->settings->files as $file) {
                if (is_object($file)) {
                    $data->attach($file->getRealPath(), [
                        'as'    => $file->getClientOriginalName(),
                        'mime'  => $file->getMimeType(),
                    ]);
                }
                if (is_array($file)) {
                    $data->attach($file['path'], [
                        'as' => $file['name']
                    ]);
                }
            }
        }

        return $data;
    }
}
