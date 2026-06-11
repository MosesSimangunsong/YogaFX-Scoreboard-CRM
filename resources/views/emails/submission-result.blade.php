<!DOCTYPE html>
<html lang="en">
    <body style="margin:0;padding:32px;background:#0f1115;color:#f8fafc;font-family:Arial,sans-serif;">
        <div style="max-width:640px;margin:0 auto;background:#171a20;border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:32px;">
            <div style="font-size:12px;font-weight:700;letter-spacing:0.22em;text-transform:uppercase;color:#d1a15b;">
                YogaFX Scoreboard
            </div>
            <h1 style="margin:18px 0 0;font-size:30px;line-height:1.2;color:#ffffff;">
                Your result is ready
            </h1>
            <p style="margin:18px 0 0;font-size:15px;line-height:1.8;color:rgba(255,255,255,0.72);">
                Hi {{ $submission->participant->first_name ?: 'there' }}, your {{ $submission->scoreboard->title }}
                result has been prepared by the YogaFX Scoreboard app.
            </p>

            <div style="margin-top:24px;padding:18px 20px;border-radius:18px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);">
                <div style="font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:rgba(255,255,255,0.45);">
                    Overall Score
                </div>
                <div style="margin-top:8px;font-size:28px;font-weight:700;color:#ffffff;">
                    {{ $submission->overall_score ?? 'N/A' }}
                </div>
            </div>

            @if($submission->result_title)
                <div style="margin-top:18px;padding:18px 20px;border-radius:18px;background:rgba(209,161,91,0.12);border:1px solid rgba(209,161,91,0.24);">
                    <div style="font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:#f0d5a8;">
                        Result Range
                    </div>
                    <div style="margin-top:8px;font-size:22px;font-weight:700;color:#ffffff;">
                        {{ $submission->result_title }}
                    </div>
                    @if($submission->result_description)
                        <p style="margin:10px 0 0;font-size:14px;line-height:1.8;color:rgba(255,255,255,0.74);">
                            {{ $submission->result_description }}
                        </p>
                    @endif
                </div>
            @endif

            <p style="margin:24px 0 0;font-size:14px;line-height:1.8;color:rgba(255,255,255,0.65);">
                The PDF version of your result report is attached to this email.
            </p>
        </div>
    </body>
</html>
