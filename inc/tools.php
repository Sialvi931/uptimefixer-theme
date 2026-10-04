<?php
/**
 * Tool HTML interfaces. One function per tool slug.
 * Called by single-alltool.php template via call_user_func().
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ================================================================
   AGE CALCULATOR — Enhanced (matches mockup Image 9)
   ================================================================ */
function alltools_tool_age_calculator() { ?>

<!-- ─── TOP: Form + Results ─── -->
<div class="at-tool-grid at-tool-grid-2">

    <div class="at-card">
        <div class="at-card-head">
            <div class="at-card-head-icon" style="background:#D1FAE5;color:#10B981;">
                <?php echo alltools_icon_svg( 'user', 22 ); ?>
            </div>
            <h2>Calculate Your Age</h2>
        </div>
        <div class="at-form">
            <div class="at-field">
                <label for="age-dob">Date of Birth</label>
                <div class="at-input-icon-wrap">
                    <span class="at-input-icon"><?php echo alltools_icon_svg('calendar',16); ?></span>
                    <input type="date" id="age-dob" class="at-input at-input-with-icon" required>
                </div>
            </div>
            <div class="at-field">
                <label for="age-asof">Calculate Age As Of</label>
                <div class="at-input-icon-wrap">
                    <span class="at-input-icon"><?php echo alltools_icon_svg('calendar',16); ?></span>
                    <input type="date" id="age-asof" class="at-input at-input-with-icon">
                </div>
                <small class="at-help">Leave as today's date or select any other date</small>
            </div>
            <button id="age-calc-btn" class="at-btn at-btn-primary at-btn-block">
                <?php echo alltools_icon_svg( 'calculator', 18 ); ?> Calculate Age <span class="at-btn-arrow">→</span>
            </button>
            <div class="at-privacy">
                <?php echo alltools_icon_svg( 'shield', 16 ); ?>
                <div>Your data is private and processed in your browser where possible.</div>
            </div>
        </div>
    </div>

    <div class="at-card" id="age-results-card">
        <div class="at-card-head at-card-head-split">
            <h2>Your Age <span class="at-badge at-badge-soft" id="age-asof-label">as of today</span></h2>
        </div>
        <div class="at-age-primary-grid">
            <div class="at-age-primary-stat"><div class="at-stat-icon" style="background:#D1FAE5;color:#10B981;"><?php echo alltools_icon_svg('calendar',18); ?></div><div class="at-stat-num" id="age-years">—</div><div class="at-stat-lbl">Years</div></div>
            <div class="at-age-primary-stat"><div class="at-stat-icon" style="background:#EDE9FE;color:#8B5CF6;"><?php echo alltools_icon_svg('calendar',18); ?></div><div class="at-stat-num" id="age-months">—</div><div class="at-stat-lbl">Months</div></div>
            <div class="at-age-primary-stat"><div class="at-stat-icon" style="background:#FED7AA;color:#F97316;"><?php echo alltools_icon_svg('calendar',18); ?></div><div class="at-stat-num" id="age-days">—</div><div class="at-stat-lbl">Days</div></div>
        </div>
        <div class="at-age-totals-grid" style="margin-top:16px;">
            <div class="at-age-total-stat"><div class="at-stat-icon" style="background:#DBEAFE;color:#2563EB;"><?php echo alltools_icon_svg('calendar',16); ?></div><div class="at-stat-num at-stat-num-sm" id="age-tweeks">—</div><div class="at-stat-lbl">Total Weeks</div></div>
            <div class="at-age-total-stat"><div class="at-stat-icon" style="background:#FCE7F3;color:#EC4899;"><?php echo alltools_icon_svg('calendar',16); ?></div><div class="at-stat-num at-stat-num-sm" id="age-thours">—</div><div class="at-stat-lbl">Total Hours</div></div>
            <div class="at-age-total-stat"><div class="at-stat-icon" style="background:#CCFBF1;color:#14B8A6;"><?php echo alltools_icon_svg('calendar',16); ?></div><div class="at-stat-num at-stat-num-sm" id="age-tmins">—</div><div class="at-stat-lbl">Total Minutes</div></div>
        </div>
        <div class="at-result-banner at-result-banner-alive" id="age-result-banner" style="display:none;">
            🎉 You have been alive for <strong id="age-alive">0</strong> days!
        </div>
    </div>

</div>

<!-- ─── Info Cards Row ─── -->
<div class="at-age-info-strip" id="age-info-strip" style="display:none;margin-top:24px;">
    <div class="at-age-info-grid">
        <div class="at-age-info-card">
            <div class="at-age-info-icon" style="background:#FEE2E2;color:#EF4444;">🎂</div>
            <div class="at-age-info-label">Next Birthday</div>
            <div class="at-age-info-sub">Your next birthday is in</div>
            <div class="at-age-info-val" id="age-bday-days" style="color:#EF4444;">—</div>
            <div class="at-age-info-note" id="age-bday-date">—</div>
        </div>
        <div class="at-age-info-card">
            <div class="at-age-info-icon" style="background:#DBEAFE;color:#2563EB;">🎁</div>
            <div class="at-age-info-label">Birthday Day Finder</div>
            <div class="at-age-info-sub">You were born on a</div>
            <div class="at-age-info-val" id="age-born-weekday" style="color:#2563EB;">—</div>
            <div class="at-age-info-note" id="age-next-weekday">—</div>
        </div>
        <div class="at-age-info-card">
            <div class="at-age-info-icon" style="background:#EDE9FE;color:#8B5CF6;">⭐</div>
            <div class="at-age-info-label">Zodiac Sign</div>
            <div class="at-age-info-sub">Your zodiac sign is</div>
            <div class="at-age-info-val" id="age-zodiac" style="color:#8B5CF6;">—</div>
            <div class="at-age-info-note" id="age-zodiac-dates">—</div>
        </div>
        <div class="at-age-info-card">
            <div class="at-age-info-icon" style="background:#FED7AA;color:#F97316;">🐾</div>
            <div class="at-age-info-label">Chinese Zodiac</div>
            <div class="at-age-info-sub">Your Chinese sign is</div>
            <div class="at-age-info-val" id="age-chinese-zodiac" style="color:#F97316;">—</div>
            <div class="at-age-info-note" id="age-chinese-year">—</div>
        </div>
        <div class="at-age-info-card">
            <div class="at-age-info-icon" style="background:#D1FAE5;color:#10B981;">👥</div>
            <div class="at-age-info-label">Generation</div>
            <div class="at-age-info-sub">You belong to</div>
            <div class="at-age-info-val" id="age-generation" style="color:#10B981;">—</div>
            <div class="at-age-info-note" id="age-generation-range">—</div>
        </div>
        </div>
    </div>
</div>

<!-- ─── Age Difference Calculator ─── -->
<div class="at-card" style="margin-top:24px;">
    <div class="at-card-head">
        <div class="at-card-head-icon" style="background:#DBEAFE;color:#2563EB;"><?php echo alltools_icon_svg('calculator',20); ?></div>
        <h2>Age Difference Calculator</h2>
    </div>
    <div class="at-age-diff-form">
        <div class="at-field">
            <label for="diff-from">From Date</label>
            <div class="at-input-icon-wrap"><span class="at-input-icon"><?php echo alltools_icon_svg('calendar',16); ?></span><input type="date" id="diff-from" class="at-input at-input-with-icon"></div>
        </div>
        <div class="at-field">
            <label for="diff-to">To Date</label>
            <div class="at-input-icon-wrap"><span class="at-input-icon"><?php echo alltools_icon_svg('calendar',16); ?></span><input type="date" id="diff-to" class="at-input at-input-with-icon"></div>
        </div>
        <button id="diff-calc" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('calculator',16); ?> Calculate Difference <span class="at-btn-arrow">→</span></button>
    </div>
    <div id="diff-result" class="at-diff-result" style="display:none;">
        <div class="at-diff-header"><small class="at-help" id="diff-label">Difference between — and —</small></div>
        <div class="at-diff-stats">
            <div class="at-diff-stat"><div class="at-stat-num" id="diff-years">—</div><div class="at-stat-lbl">Years</div></div>
            <div class="at-diff-stat"><div class="at-stat-num" id="diff-months">—</div><div class="at-stat-lbl">Months</div></div>
            <div class="at-diff-stat"><div class="at-stat-num" id="diff-days">—</div><div class="at-stat-lbl">Days</div></div>
            <div class="at-diff-stat at-diff-stat-highlight"><div class="at-stat-num" id="diff-tdays" style="color:#2563EB;">—</div><div class="at-stat-lbl">Total Days</div></div>
        </div>
    </div>
</div>

<!-- ─── CTAs ─── -->
<div class="at-age-cta-bar" style="margin-top:24px;">
    <a href="<?php echo esc_url( alltools_tool_url('check-friends-age') ); ?>" class="at-age-cta-item">
        <span class="at-age-cta-icon" style="background:#D1FAE5;color:#10B981;"><?php echo alltools_icon_svg('user',20); ?></span>
        <div><strong>Check Friend's Age</strong><span>Enter your friend's birth date</span></div>
    </a>
    <a href="<?php echo esc_url( alltools_tool_url('share-age-result') ); ?>" class="at-age-cta-item">
        <span class="at-age-cta-icon" style="background:#EDE9FE;color:#8B5CF6;"><?php echo alltools_icon_svg('heart',20); ?></span>
        <div><strong>Share Result</strong><span>Share your age with friends</span></div>
    </a>
    <div class="at-age-cta-item at-age-cta-clickable" id="age-cal-cta" style="cursor:pointer;">
        <span class="at-age-cta-icon" style="background:#FED7AA;color:#F97316;"><?php echo alltools_icon_svg('calendar',20); ?></span>
        <div><strong>Add Birthday to Calendar</strong><span>Never miss an important date</span></div>
    </div>
</div>

<?php }


/* ================================================================
   PERCENTAGE CALCULATOR (5 modes)
   ================================================================ */
function alltools_tool_percentage_calculator() { ?>
<div class="at-card">
    <div class="at-tabs" id="pct-tabs">
        <button class="at-tab is-active" data-mode="basic">% of a Number</button>
        <button class="at-tab" data-mode="change">% Change</button>
        <button class="at-tab" data-mode="isof">X is what % of Y</button>
        <button class="at-tab" data-mode="add">Add %</button>
        <button class="at-tab" data-mode="sub">Subtract %</button>
    </div>

    <div class="at-pct-mode" data-mode="basic">
        <p class="at-help-text">What is <strong>X%</strong> of <strong>Y</strong>?</p>
        <div class="at-grid-2">
            <div class="at-field"><label>Percentage (X)</label><input type="number" class="at-input pct-input" id="pct-basic-x" placeholder="25"></div>
            <div class="at-field"><label>Number (Y)</label><input type="number" class="at-input pct-input" id="pct-basic-y" placeholder="200"></div>
        </div>
    </div>
    <div class="at-pct-mode" data-mode="change" style="display:none;">
        <p class="at-help-text">% change from <strong>X</strong> to <strong>Y</strong></p>
        <div class="at-grid-2">
            <div class="at-field"><label>From (X)</label><input type="number" class="at-input pct-input" id="pct-change-x" placeholder="100"></div>
            <div class="at-field"><label>To (Y)</label><input type="number" class="at-input pct-input" id="pct-change-y" placeholder="150"></div>
        </div>
    </div>
    <div class="at-pct-mode" data-mode="isof" style="display:none;">
        <p class="at-help-text"><strong>X</strong> is what % of <strong>Y</strong>?</p>
        <div class="at-grid-2">
            <div class="at-field"><label>Number (X)</label><input type="number" class="at-input pct-input" id="pct-isof-x" placeholder="50"></div>
            <div class="at-field"><label>Total (Y)</label><input type="number" class="at-input pct-input" id="pct-isof-y" placeholder="200"></div>
        </div>
    </div>
    <div class="at-pct-mode" data-mode="add" style="display:none;">
        <p class="at-help-text">Add <strong>X%</strong> to <strong>Y</strong></p>
        <div class="at-grid-2">
            <div class="at-field"><label>Percentage (X)</label><input type="number" class="at-input pct-input" id="pct-add-x" placeholder="15"></div>
            <div class="at-field"><label>Number (Y)</label><input type="number" class="at-input pct-input" id="pct-add-y" placeholder="100"></div>
        </div>
    </div>
    <div class="at-pct-mode" data-mode="sub" style="display:none;">
        <p class="at-help-text">Subtract <strong>X%</strong> from <strong>Y</strong></p>
        <div class="at-grid-2">
            <div class="at-field"><label>Percentage (X)</label><input type="number" class="at-input pct-input" id="pct-sub-x" placeholder="20"></div>
            <div class="at-field"><label>Number (Y)</label><input type="number" class="at-input pct-input" id="pct-sub-y" placeholder="80"></div>
        </div>
    </div>

    <div class="at-result-box">
        <div class="at-result-lbl">Result</div>
        <div class="at-result-num" id="pct-result">—</div>
        <button class="at-btn at-btn-outline at-btn-sm" id="pct-copy"><?php echo alltools_icon_svg('copy',14); ?> Copy Result</button>
    </div>
</div>
<?php }

/* ================================================================
   EMI CALCULATOR
   ================================================================ */
function alltools_tool_emi_calculator() { ?>
<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h2>Loan Details</h2>
        <div class="at-form">
            <div class="at-field">
                <label>Loan Amount</label>
                <input type="number" id="emi-principal" class="at-input" placeholder="1000000" value="1000000">
            </div>
            <div class="at-field">
                <label>Interest Rate (% per annum)</label>
                <input type="number" id="emi-rate" class="at-input" step="0.01" placeholder="8.5" value="8.5">
            </div>
            <div class="at-field">
                <label>Loan Tenure (Years)</label>
                <input type="number" id="emi-years" class="at-input" placeholder="5" value="5">
            </div>
            <button id="emi-calc-btn" class="at-btn at-btn-primary at-btn-block">
                <?php echo alltools_icon_svg( 'calculator', 18 ); ?>
                Calculate EMI
            </button>
        </div>
    </div>
    <div class="at-card">
        <h2>EMI Summary</h2>
        <div class="at-emi-summary">
            <div class="at-emi-item">
                <div class="at-emi-lbl">Monthly EMI</div>
                <div class="at-emi-val" id="emi-monthly">—</div>
            </div>
            <div class="at-emi-item">
                <div class="at-emi-lbl">Total Interest</div>
                <div class="at-emi-val" id="emi-interest">—</div>
            </div>
            <div class="at-emi-item">
                <div class="at-emi-lbl">Total Payment</div>
                <div class="at-emi-val" id="emi-total">—</div>
            </div>
        </div>
    </div>
</div>
<div class="at-card" style="margin-top:24px;">
    <h2>Amortization Schedule</h2>
    <div class="at-table-wrap">
        <table class="at-table" id="emi-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>EMI</th>
                    <th>Principal</th>
                    <th>Interest</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody><tr><td colspan="5" class="at-empty">Enter loan details and click Calculate</td></tr></tbody>
        </table>
    </div>
</div>
<?php }

/* ================================================================
   IMAGE CONVERTER
   ================================================================ */
function alltools_tool_image_converter() { ?>
<div class="at-card at-card-pad-lg">
    <div class="at-dropzone" id="ic-dropzone">
        <div class="at-dropzone-icon"><?php echo alltools_icon_svg( 'upload', 32 ); ?></div>
        <h3>Convert Your Images</h3>
        <p>Drag &amp; drop your image here or click to browse</p>
        <button class="at-btn at-btn-primary" id="ic-pick-btn">
            <?php echo alltools_icon_svg( 'image', 18 ); ?> Choose Image
        </button>
        <input type="file" id="ic-file" accept="image/jpeg,image/png,image/webp" hidden>
        <p class="at-dropzone-hint">Supports: JPG, PNG, WebP (Max 10MB)</p>
    </div>

    <div class="at-grid-3" style="margin-top:24px;">
        <div class="at-field">
            <label>Convert from</label>
            <div class="at-pseudo-input" id="ic-from-label"><?php echo alltools_icon_svg( 'image', 16 ); ?> <span>Auto Detect</span></div>
        </div>
        <div class="at-arrow-cell"><?php echo alltools_icon_svg( 'arrow', 22 ); ?></div>
        <div class="at-field">
            <label>Convert to</label>
            <select id="ic-target" class="at-select">
                <option value="">Select format</option>
                <option value="image/png">PNG</option>
                <option value="image/jpeg">JPG</option>
                <option value="image/webp">WebP</option>
            </select>
        </div>
    </div>

    <div style="text-align:center;margin-top:16px;">
        <button class="at-btn at-btn-primary at-btn-lg" id="ic-convert-btn" disabled>Convert Image</button>
    </div>

    <div class="at-result-area" id="ic-result" style="display:none;">
        <div class="at-preview-wrap">
            <img id="ic-preview" alt="Preview">
        </div>
        <div class="at-btn-row">
            <button class="at-btn at-btn-success" id="ic-download"><?php echo alltools_icon_svg('download',16); ?> Download Converted</button>
        </div>
    </div>
</div>

<div class="at-section-block">
    <div class="at-section-block-head">
        <h2>All Conversion Tools</h2>
        <a href="<?php echo esc_url( alltools_category_url( 'image-tools' ) ); ?>" class="at-link">View All Image Tools</a>
    </div>
    <div class="at-grid-5">
        <?php
        $conversions = array(
            array( 'JPG to PNG',  'JPG',  '#8B5CF6', '#EDE9FE', 'jpeg', 'png',  'purple' ),
            array( 'PNG to JPG',  'PNG',  '#10B981', '#D1FAE5', 'png',  'jpeg', 'green'  ),
            array( 'WebP to JPG', 'WebP', '#EC4899', '#FCE7F3', 'webp', 'jpeg', 'pink'   ),
            array( 'JPG to WebP', 'JPG',  '#2563EB', '#DBEAFE', 'jpeg', 'webp', 'blue'   ),
            array( 'PNG to WebP', 'PNG',  '#8B5CF6', '#EDE9FE', 'png',  'webp', 'purple' ),
        );
        foreach ( $conversions as $c ) :
        ?>
        <article class="at-mini-card">
            <span class="at-mini-badge" style="background:<?php echo esc_attr( $c[3] ); ?>;color:<?php echo esc_attr( $c[2] ); ?>;"><?php echo esc_html( $c[1] ); ?></span>
            <h4><?php echo esc_html( $c[0] ); ?></h4>
            <p>Convert <?php echo esc_html( $c[1] ); ?> images to <?php echo esc_html( strtoupper( $c[5] ) ); ?> format.</p>
            <a href="#ic-dropzone" class="at-mini-link" style="color:<?php echo esc_attr( $c[2] ); ?>;">Convert</a>
        </article>
        <?php endforeach; ?>
    </div>
</div>
<?php }

/* ================================================================
   IMAGE RESIZER
   ================================================================ */
function alltools_tool_image_resizer() { ?>
<div class="at-tool-grid at-tool-grid-resizer">

    <div class="at-card">
        <h2 class="at-step-label"><span class="at-step-num">1</span> Upload Your Image</h2>
        <div class="at-dropzone at-dropzone-sm" id="ir-dropzone">
            <div class="at-dropzone-icon at-dropzone-icon-blue"><?php echo alltools_icon_svg( 'upload', 28 ); ?></div>
            <p><strong>Drag &amp; drop your image here</strong></p>
            <p>or <span class="at-link">click to browse</span></p>
            <p class="at-dropzone-hint">JPG, PNG, WebP up to 10MB</p>
            <input type="file" id="ir-file" accept="image/*" hidden>
        </div>

        <h2 class="at-step-label" style="margin-top:24px;"><span class="at-step-num">2</span> Resize Options</h2>
        <div class="at-grid-2">
            <div class="at-field">
                <label>Width (px)</label>
                <input type="number" id="ir-w" class="at-input" value="1200">
            </div>
            <div class="at-field">
                <label>Height (px)</label>
                <input type="number" id="ir-h" class="at-input" value="800">
            </div>
        </div>
        <div class="at-toggle-row">
            <label class="at-toggle">
                <input type="checkbox" id="ir-lock" checked>
                <span class="at-toggle-track"><span class="at-toggle-dot"></span></span>
                <span>Lock Aspect Ratio</span>
            </label>
        </div>

        <div class="at-field">
            <label>Format</label>
            <select id="ir-format" class="at-select">
                <option value="image/jpeg">JPG</option>
                <option value="image/png">PNG</option>
                <option value="image/webp">WebP</option>
            </select>
        </div>
        <div class="at-field">
            <label>Quality <span id="ir-q-val" style="float:right;">90%</span></label>
            <input type="range" id="ir-q" class="at-range" min="10" max="100" value="90">
        </div>

        <h3 style="margin:24px 0 12px;">Popular Presets</h3>
        <div class="at-presets">
            <button class="at-preset" data-w="1080" data-h="1080"><span class="at-preset-icon" style="background:#FCE7F3;color:#EC4899;">📷</span><strong>Instagram Post</strong><span>1080 × 1080</span></button>
            <button class="at-preset" data-w="820" data-h="312"><span class="at-preset-icon" style="background:#DBEAFE;color:#2563EB;">📘</span><strong>Facebook Cover</strong><span>820 × 312</span></button>
            <button class="at-preset" data-w="1280" data-h="720"><span class="at-preset-icon" style="background:#FEE2E2;color:#EF4444;">▶</span><strong>YouTube Thumbnail</strong><span>1280 × 720</span></button>
            <button class="at-preset" data-w="1024" data-h="512"><span class="at-preset-icon" style="background:#DBEAFE;color:#2563EB;">𝕏</span><strong>Twitter Post</strong><span>1024 × 512</span></button>
            <button class="at-preset" data-w="1200" data-h="627"><span class="at-preset-icon" style="background:#DBEAFE;color:#2563EB;">in</span><strong>LinkedIn Post</strong><span>1200 × 627</span></button>
            <button class="at-preset" data-w="" data-h=""><span class="at-preset-icon" style="background:#EDE9FE;color:#8B5CF6;">⊞</span><strong>Custom Size</strong><span>Set Manually</span></button>
        </div>

        <button id="ir-resize-btn" class="at-btn at-btn-primary at-btn-block" style="margin-top:16px;">
            <?php echo alltools_icon_svg('resize',18); ?> Resize Image
        </button>
    </div>

    <div class="at-card">
        <h2>Preview</h2>
        <div class="at-preview-wrap at-preview-wrap-lg">
            <img id="ir-preview" alt="Preview" style="display:none;">
            <div class="at-preview-placeholder" id="ir-placeholder">Upload an image to preview</div>
        </div>
        <div id="ir-info" style="display:none;margin-top:12px;">
            <p><strong id="ir-name">sample-image.jpg</strong></p>
            <p class="at-help"><span id="ir-size">—</span> · <span id="ir-orig-dim">—</span></p>
        </div>

        <h2 style="margin-top:24px;">Output Details</h2>
        <span class="at-badge at-badge-success" id="ir-ready-badge" style="display:none;">Ready</span>
        <div class="at-output-list">
            <div class="at-output-row"><div class="at-output-icon" style="background:#DBEAFE;color:#2563EB;"><?php echo alltools_icon_svg('resize',16);?></div><div class="at-output-lbl">New Dimensions</div><div class="at-output-val" id="ir-out-dim">—</div></div>
            <div class="at-output-row"><div class="at-output-icon" style="background:#EDE9FE;color:#8B5CF6;"><?php echo alltools_icon_svg('image',16);?></div><div class="at-output-lbl">File Format</div><div class="at-output-val" id="ir-out-fmt">—</div></div>
            <div class="at-output-row"><div class="at-output-icon" style="background:#D1FAE5;color:#10B981;"><?php echo alltools_icon_svg('download',16);?></div><div class="at-output-lbl">Estimated Size</div><div class="at-output-val" id="ir-out-size">—</div></div>
            <div class="at-output-row"><div class="at-output-icon" style="background:#FED7AA;color:#F97316;"><?php echo alltools_icon_svg('star',16);?></div><div class="at-output-lbl">Quality</div><div class="at-output-val" id="ir-out-q">—</div></div>
        </div>

        <div class="at-result-banner at-result-banner-success" id="ir-success" style="display:none;">
            <?php echo alltools_icon_svg('check',16); ?>
            Your image will be resized to <strong id="ir-success-dim"></strong> with high quality.
        </div>

        <button id="ir-download-btn" class="at-btn at-btn-outline at-btn-block" style="margin-top:12px;" disabled>
            <?php echo alltools_icon_svg('download',16); ?> Download Image
        </button>
    </div>

</div>
<?php }

/* ================================================================
   FAVICON GENERATOR
   ================================================================ */
function alltools_tool_favicon_generator() { ?>
<div class="at-tool-grid at-tool-grid-2">

    <div class="at-card">
        <h2>Upload Your Logo or Image</h2>
        <div class="at-dropzone" id="fg-dropzone">
            <div class="at-dropzone-icon at-dropzone-icon-blue"><?php echo alltools_icon_svg('upload',28); ?></div>
            <p><strong>Drag &amp; drop your image here</strong></p>
            <p>or</p>
            <button class="at-btn at-btn-outline at-btn-sm" id="fg-pick">Choose Image</button>
            <p class="at-dropzone-hint">PNG, JPG, SVG up to 5MB</p>
            <input type="file" id="fg-file" accept="image/*" hidden>
        </div>

        <h3 style="margin-top:24px;">Background</h3>
        <div class="at-radio-group">
            <label class="at-radio-pill is-active"><input type="radio" name="fg-bg" value="transparent" checked> Transparent</label>
            <label class="at-radio-pill"><input type="radio" name="fg-bg" value="custom"> Custom Color</label>
        </div>
        <input type="color" id="fg-bg-color" class="at-color" value="#ffffff" style="display:none;">

        <h3 style="margin-top:20px;">Shape</h3>
        <div class="at-shape-grid">
            <button class="at-shape is-active" data-shape="square"><div class="at-shape-box at-shape-square"></div><span>Square</span></button>
            <button class="at-shape" data-shape="circle"><div class="at-shape-box at-shape-circle"></div><span>Circle</span></button>
            <button class="at-shape" data-shape="rounded"><div class="at-shape-box at-shape-rounded"></div><span>Rounded</span></button>
            <button class="at-shape" data-shape="squircle"><div class="at-shape-box at-shape-squircle"></div><span>Squircle</span></button>
        </div>

        <button id="fg-generate-btn" class="at-btn at-btn-primary at-btn-block" style="margin-top:20px;" disabled>
            <?php echo alltools_icon_svg('star',16); ?> Generate Favicon
        </button>
        <div class="at-privacy">
            <?php echo alltools_icon_svg('shield',16); ?>
            <div><strong>Your images are private and not stored.</strong> All processing is done in your browser.</div>
        </div>
    </div>

    <div class="at-card">
        <h2>Favicon Preview</h2>
        <div class="at-favicon-grid" id="fg-grid">
            <?php $sizes = array(16,32,48,64,96,128,180,192); foreach ( $sizes as $s ) : ?>
            <div class="at-favicon-tile">
                <div class="at-favicon-checker"><canvas id="fg-c-<?php echo $s; ?>" width="<?php echo $s; ?>" height="<?php echo $s; ?>"></canvas></div>
                <strong><?php echo $s; ?>x<?php echo $s; ?></strong>
                <span>PNG</span>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="at-btn-row" style="margin-top:16px;">
            <button class="at-btn at-btn-primary" id="fg-download-all" disabled><?php echo alltools_icon_svg('download',16); ?> Download All (ZIP)</button>
            <button class="at-btn at-btn-outline" id="fg-download-each" disabled>Download Individual ▾</button>
        </div>
        <div class="at-result-banner at-result-banner-success" id="fg-ready" style="display:none;">
            <?php echo alltools_icon_svg('check',16); ?>
            All sizes are ready to use! Add them to your website.
        </div>
    </div>

</div>

<div class="at-card at-card-pad-lg" style="margin-top:24px;">
    <div class="at-card-head-split">
        <div>
            <h2>Add to Your Website</h2>
            <p class="at-help">Copy and paste the code below in the <code>&lt;head&gt;</code> section of your HTML.</p>
        </div>
        <button class="at-btn at-btn-outline at-btn-sm" id="fg-copy-code"><?php echo alltools_icon_svg('copy',14); ?> Copy Code</button>
    </div>
    <pre class="at-code-block" id="fg-code"><span class="at-tag">&lt;link</span> <span class="at-attr">rel</span>=<span class="at-str">"icon"</span> <span class="at-attr">type</span>=<span class="at-str">"image/png"</span> <span class="at-attr">sizes</span>=<span class="at-str">"16x16"</span> <span class="at-attr">href</span>=<span class="at-str">"/favicon-16x16.png"</span><span class="at-tag">&gt;</span>
<span class="at-tag">&lt;link</span> <span class="at-attr">rel</span>=<span class="at-str">"icon"</span> <span class="at-attr">type</span>=<span class="at-str">"image/png"</span> <span class="at-attr">sizes</span>=<span class="at-str">"32x32"</span> <span class="at-attr">href</span>=<span class="at-str">"/favicon-32x32.png"</span><span class="at-tag">&gt;</span>
<span class="at-tag">&lt;link</span> <span class="at-attr">rel</span>=<span class="at-str">"icon"</span> <span class="at-attr">type</span>=<span class="at-str">"image/png"</span> <span class="at-attr">sizes</span>=<span class="at-str">"48x48"</span> <span class="at-attr">href</span>=<span class="at-str">"/favicon-48x48.png"</span><span class="at-tag">&gt;</span>
<span class="at-tag">&lt;link</span> <span class="at-attr">rel</span>=<span class="at-str">"icon"</span> <span class="at-attr">type</span>=<span class="at-str">"image/png"</span> <span class="at-attr">sizes</span>=<span class="at-str">"64x64"</span> <span class="at-attr">href</span>=<span class="at-str">"/favicon-64x64.png"</span><span class="at-tag">&gt;</span>
<span class="at-tag">&lt;link</span> <span class="at-attr">rel</span>=<span class="at-str">"icon"</span> <span class="at-attr">type</span>=<span class="at-str">"image/png"</span> <span class="at-attr">sizes</span>=<span class="at-str">"96x96"</span> <span class="at-attr">href</span>=<span class="at-str">"/favicon-96x96.png"</span><span class="at-tag">&gt;</span>
<span class="at-tag">&lt;link</span> <span class="at-attr">rel</span>=<span class="at-str">"icon"</span> <span class="at-attr">type</span>=<span class="at-str">"image/png"</span> <span class="at-attr">sizes</span>=<span class="at-str">"128x128"</span> <span class="at-attr">href</span>=<span class="at-str">"/favicon-128x128.png"</span><span class="at-tag">&gt;</span>
<span class="at-tag">&lt;link</span> <span class="at-attr">rel</span>=<span class="at-str">"apple-touch-icon"</span> <span class="at-attr">sizes</span>=<span class="at-str">"180x180"</span> <span class="at-attr">href</span>=<span class="at-str">"/apple-touch-icon.png"</span><span class="at-tag">&gt;</span>
<span class="at-tag">&lt;link</span> <span class="at-attr">rel</span>=<span class="at-str">"icon"</span> <span class="at-attr">type</span>=<span class="at-str">"image/png"</span> <span class="at-attr">sizes</span>=<span class="at-str">"192x192"</span> <span class="at-attr">href</span>=<span class="at-str">"/android-chrome-192x192.png"</span><span class="at-tag">&gt;</span></pre>
</div>
<?php }

/* ================================================================
   WORD COUNTER
   ================================================================ */
function alltools_tool_word_counter() { ?>
<div class="at-tool-grid at-tool-grid-2">

    <div class="at-card">
        <div class="at-card-head-split">
            <h2>Your Text</h2>
            <span class="at-counter-meta"><span id="wc-cur">0</span> / 100,000 characters</span>
        </div>
        <textarea id="wc-input" class="at-textarea at-textarea-lg" placeholder="Type or paste your text here..."></textarea>
        <div class="at-btn-row" style="margin-top:12px;">
            <button class="at-btn at-btn-outline at-btn-pink" id="wc-clear"><?php echo alltools_icon_svg('trash',14); ?> Clear</button>
            <button class="at-btn at-btn-outline" id="wc-copy"><?php echo alltools_icon_svg('copy',14); ?> Copy</button>
            <button class="at-btn at-btn-primary" id="wc-download"><?php echo alltools_icon_svg('download',14); ?> Download Text</button>
        </div>
    </div>

    <div class="at-card">
        <h2>Text Statistics</h2>
        <ul class="at-stat-list">
            <li><span class="at-stat-list-icon" style="background:#D1FAE5;color:#10B981;"><?php echo alltools_icon_svg('word',16); ?></span><div class="at-stat-list-lbl">Word Count</div><div class="at-stat-list-val" id="wc-words">0</div></li>
            <li><span class="at-stat-list-icon" style="background:#DBEAFE;color:#2563EB;"><strong>A</strong></span><div class="at-stat-list-lbl">Character Count</div><div class="at-stat-list-val" id="wc-chars">0</div></li>
            <li><span class="at-stat-list-icon" style="background:#EDE9FE;color:#8B5CF6;">#</span><div class="at-stat-list-lbl">Characters (No Spaces)</div><div class="at-stat-list-val" id="wc-chars-ns">0</div></li>
            <li><span class="at-stat-list-icon" style="background:#FED7AA;color:#F97316;">≡</span><div class="at-stat-list-lbl">Sentences</div><div class="at-stat-list-val" id="wc-sentences">0</div></li>
            <li><span class="at-stat-list-icon" style="background:#FCE7F3;color:#EC4899;">¶</span><div class="at-stat-list-lbl">Paragraphs</div><div class="at-stat-list-val" id="wc-paragraphs">0</div></li>
            <li><span class="at-stat-list-icon" style="background:#CCFBF1;color:#14B8A6;">⏱</span><div class="at-stat-list-lbl">Reading Time</div><div class="at-stat-list-val"><span id="wc-read">0</span> min</div></li>
            <li><span class="at-stat-list-icon" style="background:#FEE2E2;color:#EF4444;">🎙</span><div class="at-stat-list-lbl">Speaking Time</div><div class="at-stat-list-val"><span id="wc-speak">0</span> min</div></li>
        </ul>
    </div>

</div>

<div class="at-card" style="margin-top:24px;">
    <h2>Writing Insights</h2>
    <div class="at-grid-5">
        <div class="at-insight"><div class="at-insight-icon" style="background:#D1FAE5;color:#10B981;">📖</div><div class="at-insight-num" id="wc-unique">0</div><div class="at-insight-lbl">Unique Words</div></div>
        <div class="at-insight"><div class="at-insight-icon" style="background:#EDE9FE;color:#8B5CF6;">%</div><div class="at-insight-num"><span id="wc-ease">0</span>%</div><div class="at-insight-lbl">Reading Ease</div></div>
        <div class="at-insight"><div class="at-insight-icon" style="background:#FCE7F3;color:#EC4899;">⊙</div><div class="at-insight-num"><span id="wc-density">0</span>%</div><div class="at-insight-lbl">Keyword Density</div></div>
        <div class="at-insight"><div class="at-insight-icon" style="background:#FED7AA;color:#F97316;">≈</div><div class="at-insight-num" id="wc-wps">0</div><div class="at-insight-lbl">Average Words per Sentence</div></div>
        <div class="at-insight"><div class="at-insight-icon" style="background:#FCE7F3;color:#EC4899;">Aa</div><div class="at-insight-num" id="wc-wpp">0</div><div class="at-insight-lbl">Average Words per Paragraph</div></div>
    </div>
</div>
<?php }

/* ================================================================
   CHARACTER COUNTER
   ================================================================ */
function alltools_tool_character_counter() { ?>
<div class="at-tool-grid at-tool-grid-2">

    <div class="at-card">
        <h2>Enter Your Text</h2>
        <textarea id="cc-input" class="at-textarea at-textarea-lg" placeholder="Type or paste your text here..."></textarea>
        <div class="at-card-foot">
            <div class="at-btn-row">
                <button class="at-btn at-btn-outline at-btn-sm" id="cc-clear"><?php echo alltools_icon_svg('trash',14); ?> Clear</button>
                <button class="at-btn at-btn-outline at-btn-sm" id="cc-paste"><?php echo alltools_icon_svg('copy',14); ?> Paste</button>
                <button class="at-btn at-btn-outline at-btn-sm" id="cc-copy"><?php echo alltools_icon_svg('copy',14); ?> Copy</button>
            </div>
            <span class="at-counter-meta"><span id="cc-counter">0</span> characters</span>
        </div>
    </div>

    <div class="at-card">
        <h2>Text Statistics</h2>
        <ul class="at-stat-list">
            <li><span class="at-stat-list-icon" style="background:#DBEAFE;color:#2563EB;"><strong>Aa</strong></span><div><div class="at-stat-list-lbl"><strong>Total Characters</strong></div><div class="at-stat-list-sublbl">Including spaces and punctuation</div></div><div class="at-stat-list-val" id="cc-total">0</div></li>
            <li><span class="at-stat-list-icon" style="background:#D1FAE5;color:#10B981;">⊞</span><div><div class="at-stat-list-lbl"><strong>Characters (no spaces)</strong></div><div class="at-stat-list-sublbl">Excluding spaces</div></div><div class="at-stat-list-val" id="cc-nospace">0</div></li>
            <li><span class="at-stat-list-icon" style="background:#FED7AA;color:#F97316;">≡</span><div><div class="at-stat-list-lbl"><strong>Words</strong></div><div class="at-stat-list-sublbl">Total number of words</div></div><div class="at-stat-list-val" id="cc-words">0</div></li>
            <li><span class="at-stat-list-icon" style="background:#EDE9FE;color:#8B5CF6;">!</span><div><div class="at-stat-list-lbl"><strong>Sentences</strong></div><div class="at-stat-list-sublbl">Total number of sentences</div></div><div class="at-stat-list-val" id="cc-sent">0</div></li>
            <li><span class="at-stat-list-icon" style="background:#FCE7F3;color:#EC4899;">¶</span><div><div class="at-stat-list-lbl"><strong>Paragraphs</strong></div><div class="at-stat-list-sublbl">Total number of paragraphs</div></div><div class="at-stat-list-val" id="cc-para">0</div></li>
            <li><span class="at-stat-list-icon" style="background:#CCFBF1;color:#14B8A6;">⏱</span><div><div class="at-stat-list-lbl"><strong>Est. Reading Time</strong></div><div class="at-stat-list-sublbl">Based on 200 WPM</div></div><div class="at-stat-list-val"><span id="cc-read">0</span> min</div></li>
        </ul>
    </div>

</div>

<div class="at-card" style="margin-top:24px;">
    <h2>Character Limits for Social Platforms &amp; SEO</h2>
    <div class="at-grid-4">
        <div class="at-limit-card">
            <div class="at-limit-head"><span class="at-limit-icon" style="background:#0F172A;color:white;">𝕏</span><div><strong>Twitter / X</strong><span>Post limit</span></div></div>
            <div class="at-limit-current"><strong id="cc-tw">0</strong> / 280</div>
            <div class="at-limit-bar"><div class="at-limit-fill" data-platform="tw" style="background:#10B981;"></div></div>
            <small class="at-limit-rem" data-platform="tw">280 characters remaining</small>
        </div>
        <div class="at-limit-card">
            <div class="at-limit-head"><span class="at-limit-icon" style="background:linear-gradient(135deg,#F58529,#DD2A7B,#8134AF);color:white;">📷</span><div><strong>Instagram</strong><span>Caption limit</span></div></div>
            <div class="at-limit-current"><strong id="cc-ig">0</strong> / 2,200</div>
            <div class="at-limit-bar"><div class="at-limit-fill" data-platform="ig" style="background:#EC4899;"></div></div>
            <small class="at-limit-rem" data-platform="ig">2,200 characters remaining</small>
        </div>
        <div class="at-limit-card">
            <div class="at-limit-head"><span class="at-limit-icon" style="background:#DBEAFE;color:#2563EB;">🔍</span><div><strong>Meta Description</strong><span>SEO limit</span></div></div>
            <div class="at-limit-current"><strong id="cc-seo">0</strong> / 160</div>
            <div class="at-limit-bar"><div class="at-limit-fill" data-platform="seo" style="background:#2563EB;"></div></div>
            <small class="at-limit-rem" data-platform="seo">160 characters remaining</small>
        </div>
        <div class="at-limit-card">
            <div class="at-limit-head"><span class="at-limit-icon" style="background:#D1FAE5;color:#10B981;">💬</span><div><strong>SMS</strong><span>Single message</span></div></div>
            <div class="at-limit-current"><strong id="cc-sms">0</strong> / 160</div>
            <div class="at-limit-bar"><div class="at-limit-fill" data-platform="sms" style="background:#10B981;"></div></div>
            <small class="at-limit-rem" data-platform="sms">160 characters remaining</small>
        </div>
    </div>
</div>
<?php }

/* ================================================================
   CASE CONVERTER (10 modes)
   ================================================================ */
function alltools_tool_case_converter() { ?>
<div class="at-tool-grid at-tool-grid-2">

    <div class="at-card">
        <div class="at-card-head-split">
            <h2>Input Text</h2>
            <div><span class="at-counter-meta"><span id="case-ctr">0</span> / 10,000</span> <button class="at-btn at-btn-outline at-btn-xs" id="case-clear"><?php echo alltools_icon_svg('trash',12); ?> Clear</button></div>
        </div>
        <textarea id="case-input" class="at-textarea at-textarea-lg" placeholder="Type or paste your text here..."></textarea>
        <div class="at-privacy">
            <?php echo alltools_icon_svg('shield',16); ?>
            <div><strong>Your text is processed on your device.</strong> We don't store or share your data.</div>
        </div>
    </div>

    <div class="at-card">
        <h2>Choose Case</h2>
        <div class="at-grid-2 at-case-grid">
            <button class="at-case-btn is-active" data-mode="upper"><span class="at-case-icon" style="background:#FED7AA;color:#F97316;">Aa</span> UPPERCASE</button>
            <button class="at-case-btn" data-mode="lower"><span class="at-case-icon" style="background:#D1FAE5;color:#10B981;">aa</span> lowercase</button>
            <button class="at-case-btn" data-mode="sentence"><span class="at-case-icon" style="background:#EDE9FE;color:#8B5CF6;">Aa</span> Sentence case</button>
            <button class="at-case-btn" data-mode="title"><span class="at-case-icon" style="background:#DBEAFE;color:#2563EB;">Aa</span> Title Case</button>
            <button class="at-case-btn" data-mode="capitalize"><span class="at-case-icon" style="background:#FCE7F3;color:#EC4899;">Aa</span> Capitalized Case</button>
            <button class="at-case-btn" data-mode="toggle"><span class="at-case-icon" style="background:#CCFBF1;color:#14B8A6;">⇄</span> Toggle Case</button>
            <button class="at-case-btn" data-mode="camel"><span class="at-case-icon" style="background:#FCE7F3;color:#EC4899;">cC</span> camelCase</button>
            <button class="at-case-btn" data-mode="pascal"><span class="at-case-icon" style="background:#DBEAFE;color:#2563EB;">PC</span> PascalCase</button>
            <button class="at-case-btn" data-mode="snake"><span class="at-case-icon" style="background:#EDE9FE;color:#8B5CF6;">s_</span> snake_case</button>
            <button class="at-case-btn" data-mode="kebab"><span class="at-case-icon" style="background:#D1FAE5;color:#10B981;">k-</span> kebab-case</button>
        </div>

        <h2 style="margin-top:24px;">Output <button class="at-btn at-btn-outline at-btn-xs" id="case-copy" style="float:right;"><?php echo alltools_icon_svg('copy',12); ?> Copy</button></h2>
        <div class="at-output-box" id="case-output">Your converted text will appear here...</div>
    </div>

</div>
<?php }

/* ================================================================
   REMOVE DUPLICATE LINES
   ================================================================ */
function alltools_tool_remove_duplicate_lines() { ?>
<div class="at-tool-grid at-tool-grid-cleaner">
    <div class="at-card">
        <div class="at-card-head-split"><h2>Input Text</h2><span class="at-counter-meta"><span id="rdl-stats">0 Characters · 0 Lines</span></span></div>
        <p class="at-help">Paste or type your text below.</p>
        <textarea id="rdl-input" class="at-textarea at-textarea-xl" placeholder="Paste your text here..."></textarea>
        <div class="at-toggle-row">
            <label class="at-toggle">
                <input type="checkbox" id="rdl-case" checked>
                <span class="at-toggle-track"><span class="at-toggle-dot"></span></span>
                <span>Case-sensitive matching</span>
            </label>
            <label class="at-toggle">
                <input type="checkbox" id="rdl-trim" checked>
                <span class="at-toggle-track"><span class="at-toggle-dot"></span></span>
                <span>Trim whitespace per line</span>
            </label>
        </div>
        <button class="at-btn at-btn-outline at-btn-block at-btn-green" id="rdl-run">
            <?php echo alltools_icon_svg('duplicate',16); ?> Remove Duplicate Lines
        </button>
        <div class="at-privacy">
            <?php echo alltools_icon_svg('shield',16); ?>
            <div>Your text is private and not stored. All processing is done in your browser.</div>
        </div>

        <h2 style="margin-top:24px;">Output Text <span class="at-counter-meta" style="float:right;font-weight:400;"><span id="rdl-out-stats">0 Characters · 0 Lines</span></span></h2>
        <textarea id="rdl-output" class="at-textarea at-textarea-xl at-textarea-output" readonly placeholder="Cleaned text will appear here..."></textarea>
        <div class="at-btn-row">
            <button class="at-btn at-btn-outline at-btn-green" id="rdl-copy"><?php echo alltools_icon_svg('copy',14); ?> Copy Text</button>
            <button class="at-btn at-btn-outline" id="rdl-download"><?php echo alltools_icon_svg('download',14); ?> Download .txt</button>
            <button class="at-btn at-btn-outline" id="rdl-clear"><?php echo alltools_icon_svg('trash',14); ?> Clear All</button>
        </div>
    </div>

    <div class="at-card at-summary-card">
        <h2>Cleaning Summary</h2>
        <div class="at-summary-item"><div class="at-summary-icon" style="background:#D1FAE5;color:#10B981;">≡</div><div><div class="at-summary-num" id="rdl-totals">0</div><div class="at-summary-lbl"><strong>Total Lines</strong><span>In output</span></div></div></div>
        <div class="at-summary-item"><div class="at-summary-icon" style="background:#FED7AA;color:#F97316;">⊠</div><div><div class="at-summary-num" id="rdl-dups">0</div><div class="at-summary-lbl"><strong>Duplicate Lines</strong><span>Removed</span></div></div></div>
        <div class="at-summary-item"><div class="at-summary-icon" style="background:#EDE9FE;color:#8B5CF6;">✦</div><div><div class="at-summary-num" id="rdl-unique">0</div><div class="at-summary-lbl"><strong>Unique Lines</strong><span>Preserved</span></div></div></div>
        <div class="at-summary-success" id="rdl-success" style="display:none;">
            <span class="at-summary-success-icon"><?php echo alltools_icon_svg('check',16); ?></span>
            <div><strong>Great job!</strong><br>Your text is now clean and ready to use.</div>
        </div>
    </div>
</div>
<?php }

/* ================================================================
   REMOVE EXTRA SPACES
   ================================================================ */
function alltools_tool_remove_extra_spaces() { ?>
<div class="at-tool-grid at-tool-grid-cleaner">
    <div class="at-card">
        <div class="at-card-head-split"><h2>Input Text</h2><span class="at-counter-meta"><span id="res-stats">0 Characters · 0 Lines</span></span></div>
        <p class="at-help">Paste or type your text below.</p>
        <textarea id="res-input" class="at-textarea at-textarea-xl" placeholder="Paste your text here..."></textarea>
        <div class="at-toggle-row">
            <label class="at-toggle">
                <input type="checkbox" id="res-multi" checked>
                <span class="at-toggle-track"><span class="at-toggle-dot"></span></span>
                <span>Collapse multiple spaces</span>
            </label>
            <label class="at-toggle">
                <input type="checkbox" id="res-trim" checked>
                <span class="at-toggle-track"><span class="at-toggle-dot"></span></span>
                <span>Trim line whitespace</span>
            </label>
            <label class="at-toggle">
                <input type="checkbox" id="res-blank">
                <span class="at-toggle-track"><span class="at-toggle-dot"></span></span>
                <span>Remove blank lines</span>
            </label>
        </div>
        <button class="at-btn at-btn-outline at-btn-block at-btn-pink" id="res-run">
            <?php echo alltools_icon_svg('spaces',16); ?> Remove Extra Spaces
        </button>
        <div class="at-privacy">
            <?php echo alltools_icon_svg('shield',16); ?>
            <div>Your text is private and not stored. All processing is done in your browser.</div>
        </div>

        <h2 style="margin-top:24px;">Output Text <span class="at-counter-meta" style="float:right;font-weight:400;"><span id="res-out-stats">0 Characters · 0 Lines</span></span></h2>
        <textarea id="res-output" class="at-textarea at-textarea-xl at-textarea-output" readonly placeholder="Cleaned text will appear here..."></textarea>
        <div class="at-btn-row">
            <button class="at-btn at-btn-outline at-btn-green" id="res-copy"><?php echo alltools_icon_svg('copy',14); ?> Copy Text</button>
            <button class="at-btn at-btn-outline" id="res-download"><?php echo alltools_icon_svg('download',14); ?> Download .txt</button>
            <button class="at-btn at-btn-outline" id="res-clear"><?php echo alltools_icon_svg('trash',14); ?> Clear All</button>
        </div>
    </div>

    <div class="at-card at-summary-card">
        <h2>Cleaning Summary</h2>
        <div class="at-summary-item"><div class="at-summary-icon" style="background:#D1FAE5;color:#10B981;">≡</div><div><div class="at-summary-num" id="res-totals">0</div><div class="at-summary-lbl"><strong>Total Lines</strong><span>In output</span></div></div></div>
        <div class="at-summary-item"><div class="at-summary-icon" style="background:#FED7AA;color:#F97316;">⊠</div><div><div class="at-summary-num" id="res-removed">0</div><div class="at-summary-lbl"><strong>Extra Spaces</strong><span>Removed</span></div></div></div>
        <div class="at-summary-item"><div class="at-summary-icon" style="background:#EDE9FE;color:#8B5CF6;">✦</div><div><div class="at-summary-num" id="res-clean">0</div><div class="at-summary-lbl"><strong>Whitespace Chars</strong><span>Cleaned</span></div></div></div>
        <div class="at-summary-success" id="res-success" style="display:none;">
            <span class="at-summary-success-icon"><?php echo alltools_icon_svg('check',16); ?></span>
            <div><strong>Great job!</strong><br>Your text is now clean and ready to use.</div>
        </div>
    </div>
</div>
<?php }

/* ================================================================
   QR CODE GENERATOR
   ================================================================ */
function alltools_tool_qr_code_generator() { ?>
<div class="at-tool-grid at-tool-grid-2">

    <div class="at-card">
        <h2><span class="at-step-num">1.</span> Choose QR Code Type</h2>
        <div class="at-qr-types">
            <button class="at-qr-type is-active" data-type="url"><span><?php echo alltools_icon_svg('link',18); ?></span><strong>URL</strong></button>
            <button class="at-qr-type" data-type="text"><span><?php echo alltools_icon_svg('word',18); ?></span><strong>Text</strong></button>
            <button class="at-qr-type" data-type="email"><span><?php echo alltools_icon_svg('mail',18); ?></span><strong>Email</strong></button>
            <button class="at-qr-type" data-type="phone"><span><?php echo alltools_icon_svg('phone',18); ?></span><strong>Phone</strong></button>
            <button class="at-qr-type" data-type="sms"><span><?php echo alltools_icon_svg('message',18); ?></span><strong>SMS</strong></button>
            <button class="at-qr-type" data-type="wifi"><span><?php echo alltools_icon_svg('wifi',18); ?></span><strong>WiFi</strong></button>
            <button class="at-qr-type" data-type="vcard"><span><?php echo alltools_icon_svg('contact',18); ?></span><strong>vCard</strong></button>
        </div>

        <h2 style="margin-top:24px;"><span class="at-step-num">2.</span> Enter <span id="qr-step2-label">URL</span></h2>
        <div id="qr-fields"></div>

        <h2 style="margin-top:24px;"><span class="at-step-num">3.</span> Customize Your QR Code</h2>
        <div class="at-grid-2">
            <div class="at-field"><label>Foreground Color</label>
                <div class="at-color-row"><input type="color" id="qr-fg" value="#111827"><input type="text" id="qr-fg-hex" class="at-input" value="#111827"></div>
            </div>
            <div class="at-field"><label>Background Color</label>
                <div class="at-color-row"><input type="color" id="qr-bg" value="#FFFFFF"><input type="text" id="qr-bg-hex" class="at-input" value="#FFFFFF"></div>
            </div>
        </div>

        <h3 style="margin-top:16px;">QR Code Style</h3>
        <div class="at-style-grid">
            <button class="at-style is-active" data-style="square"><span class="at-style-icon">▣</span></button>
            <button class="at-style" data-style="dots"><span class="at-style-icon">⣿</span></button>
            <button class="at-style" data-style="grid"><span class="at-style-icon">⠿</span></button>
            <button class="at-style" data-style="dotsbig"><span class="at-style-icon">⠶</span></button>
            <button class="at-style" data-style="round"><span class="at-style-icon">◌</span></button>
            <button class="at-style" data-style="frame"><span class="at-style-icon">⊟</span></button>
        </div>

        <h3 style="margin-top:16px;">Logo (Optional)</h3>
        <div class="at-grid-2">
            <div class="at-dropzone at-dropzone-xs" id="qr-logo-zone">
                <div class="at-dropzone-icon at-dropzone-icon-sm"><?php echo alltools_icon_svg('upload',16); ?></div>
                <p><strong>Upload Logo</strong></p>
                <p class="at-dropzone-hint">PNG, JPG or SVG (Max 2MB)</p>
                <input type="file" id="qr-logo" accept="image/*" hidden>
            </div>
            <div class="at-field"><label>Logo Size <span id="qr-logo-size-val" style="float:right;">20%</span></label><input type="range" id="qr-logo-size" class="at-range" min="0" max="40" value="20"></div>
        </div>
    </div>

    <div class="at-card">
        <h2>QR Code Preview</h2>
        <div class="at-grid-2"><div class="at-field"><label for="qr-size">Image size</label><select id="qr-size" class="at-select"><option value="320">320 px</option><option value="512">512 px</option><option value="1024" selected>1024 px</option></select></div><div class="at-field"><label for="qr-correction">Error correction</label><select id="qr-correction" class="at-select"><option value="M">Medium</option><option value="Q">High</option><option value="H">Highest</option></select></div></div><div class="at-qr-preview"><canvas id="qr-canvas" width="320" height="320"></canvas></div>
        <div class="at-result-banner at-result-banner-success" id="qr-banner">
            <?php echo alltools_icon_svg('check',16); ?>
            Preview your QR code; scan-test it before sharing.
        </div>
        <div class="at-grid-2">
            <button class="at-btn at-btn-primary" id="qr-download-png"><?php echo alltools_icon_svg('download',16); ?> Download PNG</button>
            <button class="at-btn at-btn-outline" id="qr-download-svg"><?php echo alltools_icon_svg('download',16); ?> Download SVG</button>
        </div>

        <h2 style="margin-top:24px;">QR Code Summary</h2>
        <div class="at-summary-rows">
            <div class="at-summary-row"><span>Type</span><strong id="qr-sum-type">URL</strong></div>
            <div class="at-summary-row"><span>Content</span><strong id="qr-sum-content">https://www.example.com</strong></div>
            <div class="at-summary-row"><span>Size</span><strong id="qr-sum-size">1024 × 1024 px</strong></div>
            <div class="at-summary-row"><span>Error Correction</span><strong>Medium (15%)</strong></div>
        </div>
    </div>
</div>

<div class="at-section-block">
    <div class="at-section-block-head">
        <h2>Supported QR Code Types</h2>
        <p class="at-help">Generate QR codes for various data types. Choose the one that fits your needs.</p>
    </div>
    <div class="at-grid-7">
        <div class="at-mini-type"><span class="at-mini-type-icon" style="background:#DBEAFE;color:#2563EB;"><?php echo alltools_icon_svg('link',18); ?></span><strong>URL</strong><span>Website links and pages</span></div>
        <div class="at-mini-type"><span class="at-mini-type-icon" style="background:#DBEAFE;color:#2563EB;"><?php echo alltools_icon_svg('word',18); ?></span><strong>Text</strong><span>Plain text messages</span></div>
        <div class="at-mini-type"><span class="at-mini-type-icon" style="background:#FCE7F3;color:#EC4899;"><?php echo alltools_icon_svg('mail',18); ?></span><strong>Email</strong><span>Email addresses and messages</span></div>
        <div class="at-mini-type"><span class="at-mini-type-icon" style="background:#D1FAE5;color:#10B981;"><?php echo alltools_icon_svg('phone',18); ?></span><strong>Phone</strong><span>Phone numbers and calls</span></div>
        <div class="at-mini-type"><span class="at-mini-type-icon" style="background:#FED7AA;color:#F97316;"><?php echo alltools_icon_svg('message',18); ?></span><strong>SMS</strong><span>SMS messages and text</span></div>
        <div class="at-mini-type"><span class="at-mini-type-icon" style="background:#DBEAFE;color:#2563EB;"><?php echo alltools_icon_svg('wifi',18); ?></span><strong>WiFi</strong><span>WiFi network credentials</span></div>
        <div class="at-mini-type"><span class="at-mini-type-icon" style="background:#EDE9FE;color:#8B5CF6;"><?php echo alltools_icon_svg('contact',18); ?></span><strong>vCard</strong><span>Contact details and information</span></div>
    </div>
</div>
<?php }

/* ================================================================
   CHECK FRIEND'S AGE
   ================================================================ */
function alltools_tool_check_friends_age() { ?>

<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <div class="at-card-head">
            <div class="at-card-head-icon" style="background:#D1FAE5;color:#10B981;"><?php echo alltools_icon_svg('user',22); ?></div>
            <h2>Enter Friend Details</h2>
        </div>
        <div class="at-form">
            <div class="at-field">
                <label for="fa-name">Friend Name</label>
                <div class="at-input-icon-wrap"><span class="at-input-icon"><?php echo alltools_icon_svg('user',16); ?></span><input type="text" id="fa-name" class="at-input at-input-with-icon" placeholder="e.g. Alex"></div>
            </div>
            <div class="at-field">
                <label for="fa-dob">Date of Birth</label>
                <div class="at-input-icon-wrap"><span class="at-input-icon"><?php echo alltools_icon_svg('calendar',16); ?></span><input type="date" id="fa-dob" class="at-input at-input-with-icon" required></div>
            </div>
            <div class="at-field">
                <label for="fa-asof">Calculate Age As Of</label>
                <div class="at-input-icon-wrap"><span class="at-input-icon"><?php echo alltools_icon_svg('calendar',16); ?></span><input type="date" id="fa-asof" class="at-input at-input-with-icon"></div>
            </div>
            <button id="fa-check-btn" class="at-btn at-btn-primary at-btn-block">
                <?php echo alltools_icon_svg('user',18); ?> Check Friend's Age <span class="at-btn-arrow">→</span>
            </button>
            <div class="at-privacy"><?php echo alltools_icon_svg('shield',16); ?><div>Your data is private and never stored.</div></div>
        </div>
    </div>

    <div class="at-card">
        <div class="at-card-head at-card-head-split">
            <h2>Friend's Age Summary</h2>
            <span class="at-help" id="fa-today-label">Today: —</span>
        </div>
        <div id="fa-empty-state" class="at-empty-state">
            <?php echo alltools_icon_svg('user',40); ?>
            <p>Enter your friend's details and click Check to see results.</p>
        </div>
        <div id="fa-results" style="display:none;">
            <div class="at-fa-profile">
                <div class="at-fa-avatar" id="fa-avatar">A</div>
                <div>
                    <div class="at-fa-name" id="fa-display-name">Friend</div>
                    <div class="at-fa-dob"><?php echo alltools_icon_svg('calendar',14); ?> Born on <span id="fa-born-on">—</span></div>
                </div>
            </div>
            <div class="at-age-primary-grid" style="margin-top:16px;">
                <div class="at-age-primary-stat"><div class="at-stat-icon" style="background:#D1FAE5;color:#10B981;"><?php echo alltools_icon_svg('calendar',16); ?></div><div class="at-stat-num" id="fa-years">—</div><div class="at-stat-lbl">Years</div></div>
                <div class="at-age-primary-stat"><div class="at-stat-icon" style="background:#EDE9FE;color:#8B5CF6;"><?php echo alltools_icon_svg('calendar',16); ?></div><div class="at-stat-num" id="fa-months">—</div><div class="at-stat-lbl">Months</div></div>
                <div class="at-age-primary-stat"><div class="at-stat-icon" style="background:#FED7AA;color:#F97316;"><?php echo alltools_icon_svg('calendar',16); ?></div><div class="at-stat-num" id="fa-days">—</div><div class="at-stat-lbl">Days</div></div>
                <div class="at-age-primary-stat"><div class="at-stat-icon" style="background:#DBEAFE;color:#2563EB;"><?php echo alltools_icon_svg('calendar',16); ?></div><div class="at-stat-num" id="fa-tdays">—</div><div class="at-stat-lbl">Total Days</div></div>
            </div>
            <div class="at-result-banner at-result-banner-alive" style="margin-top:16px;">
                🎉 <span id="fa-display-name2">Friend</span> has been alive for <strong id="fa-alive">0</strong> days!
            </div>
        </div>
    </div>
</div>

<div class="at-age-info-strip" id="fa-info-strip" style="display:none;margin-top:24px;">
    <div class="at-age-info-grid">
        <div class="at-age-info-card">
            <div class="at-age-info-icon" style="background:#FEE2E2;color:#EF4444;">🎂</div>
            <div class="at-age-info-label">Next Birthday</div>
            <div class="at-age-info-sub" id="fa-bday-who">Next birthday is in</div>
            <div class="at-age-info-val" id="fa-bday-days" style="color:#EF4444;">—</div>
            <div class="at-age-info-note" id="fa-bday-date">—</div>
        </div>
        <div class="at-age-info-card">
            <div class="at-age-info-icon" style="background:#DBEAFE;color:#2563EB;">🎁</div>
            <div class="at-age-info-label">Birthday Day Finder</div>
            <div class="at-age-info-sub" id="fa-born-who">Born on a</div>
            <div class="at-age-info-val" id="fa-born-weekday" style="color:#2563EB;">—</div>
            <div class="at-age-info-note" id="fa-next-weekday">—</div>
        </div>
        <div class="at-age-info-card">
            <div class="at-age-info-icon" style="background:#EDE9FE;color:#8B5CF6;">⭐</div>
            <div class="at-age-info-label">Zodiac Sign</div>
            <div class="at-age-info-sub" id="fa-zodiac-who">Zodiac sign is</div>
            <div class="at-age-info-val" id="fa-zodiac" style="color:#8B5CF6;">—</div>
            <div class="at-age-info-note" id="fa-zodiac-dates">—</div>
        </div>
        <div class="at-age-info-card">
            <div class="at-age-info-icon" style="background:#D1FAE5;color:#10B981;">👥</div>
            <div class="at-age-info-label">Generation</div>
            <div class="at-age-info-sub" id="fa-gen-who">Belongs to</div>
            <div class="at-age-info-val" id="fa-generation" style="color:#10B981;">—</div>
            <div class="at-age-info-note" id="fa-generation-range">—</div>
        </div>
        </div>
    </div>
</div>

<div class="at-age-cta-bar" id="fa-cta-bar" style="margin-top:24px;display:none;">
    <div class="at-age-cta-item at-age-cta-clickable" id="fa-share-btn">
        <span class="at-age-cta-icon" style="background:#EDE9FE;color:#8B5CF6;"><?php echo alltools_icon_svg('heart',20); ?></span>
        <div><strong>Share Friend Result</strong><span id="fa-share-who">Share age with friends</span></div>
    </div>
    <div class="at-age-cta-item at-age-cta-clickable" id="fa-copy-btn">
        <span class="at-age-cta-icon" style="background:#D1FAE5;color:#10B981;"><?php echo alltools_icon_svg('copy',20); ?></span>
        <div><strong>Copy Result</strong><span id="fa-copy-who">Copy age details</span></div>
    </div>
    <div class="at-age-cta-item at-age-cta-clickable" id="fa-cal-btn">
        <span class="at-age-cta-icon" style="background:#FED7AA;color:#F97316;"><?php echo alltools_icon_svg('calendar',20); ?></span>
        <div><strong>Add Reminder</strong><span>Set a reminder for next birthday</span></div>
    </div>
</div>

<?php }

/* ================================================================
   SHARE AGE RESULT
   ================================================================ */
function alltools_tool_share_age_result() { ?>

<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h2>Result Preview</h2>
        <div class="at-share-preview-wrap">
            <div class="at-share-card" id="share-card-preview" data-theme="classic">
                <div class="at-share-card-head">
                    <div class="at-share-card-avatar" id="sc-avatar">U</div>
                    <div>
                        <div class="at-share-card-name" id="sc-name">Your Name</div>
                        <div class="at-share-card-caption" id="sc-caption">Here's my age insight 🎉</div>
                    </div>
                    <div class="at-share-card-date">Generated on<br><span id="sc-date"><?php echo date('M d, Y'); ?></span></div>
                </div>
                <div class="at-share-stats">
                    <div class="at-share-stat"><div class="at-share-stat-num" id="sc-years">—</div><div>Years</div></div>
                    <div class="at-share-stat"><div class="at-share-stat-num" id="sc-weeks">—</div><div>Total Weeks</div></div>
                    <div class="at-share-stat"><div class="at-share-stat-num" id="sc-hours">—</div><div>Total Hours</div></div>
                    <div class="at-share-stat"><div class="at-share-stat-num" id="sc-minutes">—</div><div>Total Minutes</div></div>
                </div>
                <div class="at-share-details" id="sc-details-row">
                    <div class="at-share-detail-item"><span>🎂</span><div><small>Next Birthday</small><strong id="sc-bday">— days</strong></div></div>
                    <div class="at-share-detail-item"><span>⭐</span><div><small>Zodiac Sign</small><strong id="sc-zodiac">—</strong></div></div>
                </div>
                </div>
            </div>
        </div>
        <p class="at-help" style="margin-top:8px;text-align:center;">Customize your card on the right. To use real data, first <a href="<?php echo esc_url( alltools_tool_url('age-calculator') ); ?>">calculate your age</a>.</p>
    </div>

    <div class="at-card">
        <h2>Customize Your Share Card</h2>
        <div class="at-field" style="margin-bottom:16px;">
            <label>Theme Style</label>
            <div class="at-share-theme-grid">
                <button class="at-share-theme is-active" data-theme="classic"><span style="background:#fff;border:2px solid #2563EB;border-radius:4px;display:block;height:24px;"></span>Classic</button>
                <button class="at-share-theme" data-theme="gradient"><span style="background:linear-gradient(135deg,#667eea,#764ba2);border-radius:4px;display:block;height:24px;"></span>Gradient</button>
                <button class="at-share-theme" data-theme="minimal"><span style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:4px;display:block;height:24px;"></span>Minimal</button>
                <button class="at-share-theme" data-theme="dark"><span style="background:#0F172A;border-radius:4px;display:block;height:24px;"></span>Dark</button>
            </div>
        </div>
        <div class="at-field" style="margin-bottom:16px;">
            <label>Include in Card</label>
            <div class="at-share-toggles">
                <div class="at-share-toggle-row"><span>Age Summary (Years, Weeks, Hours, Minutes)</span><label class="at-toggle"><input type="checkbox" id="sc-inc-age" checked><span class="at-toggle-slider"></span></label></div>
                <div class="at-share-toggle-row"><span>Next Birthday Countdown</span><label class="at-toggle"><input type="checkbox" id="sc-inc-bday" checked><span class="at-toggle-slider"></span></label></div>
                <div class="at-share-toggle-row"><span>Zodiac Sign</span><label class="at-toggle"><input type="checkbox" id="sc-inc-zodiac" checked><span class="at-toggle-slider"></span></label></div>
                <div class="at-share-toggle-row"><span>Generated Date</span><label class="at-toggle"><input type="checkbox" id="sc-inc-date" checked><span class="at-toggle-slider"></span></label></div>
            </div>
        </div>
        <div class="at-field">
            <label for="sc-input-name">Recipient Name</label>
            <div class="at-input-icon-wrap"><span class="at-input-icon"><?php echo alltools_icon_svg('user',16); ?></span><input type="text" id="sc-input-name" class="at-input at-input-with-icon" placeholder="Your name"></div>
        </div>
        <div class="at-field">
            <label for="sc-input-caption">Personal Caption <small class="at-help">(Optional)</small></label>
            <textarea id="sc-input-caption" class="at-textarea" placeholder="Here's my age insight 🎉" maxlength="120" rows="2"></textarea>
            <small class="at-help" id="sc-cap-count" style="float:right;">0/120</small>
        </div>
    </div>
</div>

<div class="at-card" style="margin-top:24px;">
    <h2>Share Your Result</h2>
    <div class="at-share-btns-grid">
        <button class="at-share-btn-item" id="sc-btn-copy-link"><span class="at-share-btn-icon" style="background:#DBEAFE;color:#2563EB;"><?php echo alltools_icon_svg('link',20); ?></span><strong>Copy Link</strong><small>Share via unique link</small></button>
        <button class="at-share-btn-item" id="sc-btn-download"><span class="at-share-btn-icon" style="background:#D1FAE5;color:#10B981;"><?php echo alltools_icon_svg('download',20); ?></span><strong>Download Image</strong><small>Save as PNG</small></button>
        <button class="at-share-btn-item" id="sc-btn-whatsapp"><span class="at-share-btn-icon" style="background:#D1FAE5;color:#25D366;">💬</span><strong>Share to WhatsApp</strong><small>Share instantly</small></button>
        <button class="at-share-btn-item" id="sc-btn-facebook"><span class="at-share-btn-icon" style="background:#DBEAFE;color:#1877F2;">📘</span><strong>Share to Facebook</strong><small>Post to timeline</small></button>
        <button class="at-share-btn-item" id="sc-btn-copy-text"><span class="at-share-btn-icon" style="background:#EDE9FE;color:#8B5CF6;"><?php echo alltools_icon_svg('copy',20); ?></span><strong>Copy Result Text</strong><small>Plain text to copy</small></button>
    </div>
</div>

<div class="at-card" style="margin-top:24px;">
    <div class="at-card-head at-card-head-split"><h2>Caption Ideas</h2></div>
    <div class="at-caption-ideas-grid">
        <?php foreach([
            'Another trip around the sun 🌞 Grateful for every moment!',
            'Age is just a number, but experiences are priceless. ✨',
            "Here's my age insight! Time to celebrate life and growth 🎉",
            'Counting memories, not years. ❤️',
            'Every year is a new chapter. Excited for what\'s next! 📖',
        ] as $cap) : ?>
        <div class="at-caption-idea"><p>"<?php echo esc_html($cap); ?>"</p><button class="at-btn at-btn-outline at-btn-xs at-caption-use" data-caption="<?php echo esc_attr($cap); ?>">Use</button></div>
        <?php endforeach; ?>
    </div>
</div>

<?php }


/* ================================================================
   WEBSITE UPTIME CHECKER (Image 2)
   ================================================================ */
function alltools_tool_website_uptime_checker() { ?>
<div class="at-card">
    <div class="at-field">
        <label for="up-url">Website URL</label>
        <div class="at-input-with-btn">
            <span class="at-input-icon"><?php echo alltools_icon_svg('globe', 16); ?></span>
            <input type="url" id="up-url" class="at-input at-input-with-icon" placeholder="https://example.com">
            <button id="up-check" class="at-btn at-btn-primary">
                <?php echo alltools_icon_svg('pulse', 16); ?> Check Uptime
            </button>
        </div>
        <small class="at-help">Enter the full URL including https://</small>
    </div>

    <div id="up-result" class="at-uptime-result" style="display:none;">
        <div class="at-uptime-header">
            <div class="at-uptime-status">
                <div class="at-uptime-circle" id="up-circle"></div>
                <div>
                    <div class="at-uptime-label" id="up-label">Online</div>
                    <div class="at-uptime-desc" id="up-desc">The website is up and running.</div>
                </div>
            </div>
            <div class="at-uptime-meta">
                <div><span>Response Time</span><strong id="up-rt">—</strong></div>
                <div><span>Server</span><strong id="up-server">—</strong></div>
                <div><span>Last Checked</span><strong id="up-time">—</strong></div>
                <div><span>IP Address</span><strong id="up-ip">—</strong></div>
            </div>
        </div>
    </div>

    <div id="up-stats" class="at-grid-3" style="margin-top:20px;display:none;">
        <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#D1FAE5;color:#10B981;"><?php echo alltools_icon_svg('check',16); ?></span><div><span>Current Status</span><strong id="up-cstatus" style="color:#10B981;">Online</strong><em id="up-since">Since this check</em></div></div>
        <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#EDE9FE;color:#8B5CF6;"><?php echo alltools_icon_svg('calendar',16); ?></span><div><span>Response Time</span><strong id="up-avg" style="color:#8B5CF6;">—</strong><em id="up-avgrating">This request</em></div></div>
        <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#DBEAFE;color:#2563EB;"><?php echo alltools_icon_svg('pulse',16); ?></span><div><span>Check Type</span><strong style="color:#2563EB;">On demand</strong><em>No history or alerts</em></div></div>
    </div>
</div>
<?php }

/* ================================================================
   WEBSITE SPEED TEST (Image 3)
   ================================================================ */
function alltools_tool_website_speed_test() { ?>
<div class="at-card">
    <div class="at-field">
        <label for="sp-url">Website URL</label>
        <div class="at-input-with-btn">
            <span class="at-input-icon"><?php echo alltools_icon_svg('link', 16); ?></span>
            <input type="url" id="sp-url" class="at-input at-input-with-icon" placeholder="https://example.com">
            <button id="sp-run" class="at-btn at-btn-primary">
                <?php echo alltools_icon_svg('gauge', 16); ?> Run Speed Test
            </button>
        </div>
        <small class="at-help">This server snapshot measures the page request and inventories discoverable resources. It is not a Lighthouse or browser Core Web Vitals test.</small>
    </div>

    <div id="sp-result" style="display:none;">
        <div class="at-result-head">
            <div><h3>Results</h3><small class="at-help" id="sp-tested">Tested on —</small></div>
            <button class="at-link" id="sp-again">Test Again ↻</button>
        </div>

        <div class="at-speed-grid">
            <div class="at-speed-score">
                <svg viewBox="0 0 120 120" width="160" height="160">
                    <circle cx="60" cy="60" r="52" fill="none" stroke="#E2E8F0" stroke-width="10"/>
                    <circle id="sp-ring" cx="60" cy="60" r="52" fill="none" stroke="#10B981" stroke-width="10" stroke-dasharray="327" stroke-dashoffset="327" transform="rotate(-90 60 60)" stroke-linecap="round"/>
                </svg>
                <div class="at-speed-score-num" id="sp-score">—</div>
                <div class="at-speed-score-lbl">Server Snapshot Score</div>
                <div class="at-speed-score-rating" id="sp-rating">—</div>
            </div>
            <div class="at-speed-metrics">
                <div class="at-metric"><span>Server Fetch Time</span><strong id="sp-load">—</strong><em id="sp-load-r">—</em></div>
                <div class="at-metric"><span>First Contentful Paint</span><strong id="sp-fcp">Not measured</strong><em id="sp-fcp-r">Browser test required</em></div>
                <div class="at-metric"><span>Largest Contentful Paint</span><strong id="sp-lcp">Not measured</strong><em id="sp-lcp-r">Browser test required</em></div>
                <div class="at-metric"><span>Server Request Time</span><strong id="sp-ttfb">—</strong><em id="sp-ttfb-r">This request</em></div>
                <div class="at-metric"><span>Total Blocking Time</span><strong id="sp-tbt">Not measured</strong><em id="sp-tbt-r">Browser test required</em></div>
                <div class="at-metric"><span>Cumulative Layout Shift</span><strong id="sp-cls">Not measured</strong><em id="sp-cls-r">Browser test required</em></div>
            </div>
        </div>

        <div class="at-grid-3" style="margin-top:20px;">
            <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#D1FAE5;color:#10B981;"><?php echo alltools_icon_svg('image',16); ?></span><div><span>Estimated Transfer Size</span><strong id="sp-size">—</strong><em id="sp-size-r">Probed resources</em></div></div>
            <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#EDE9FE;color:#8B5CF6;"><?php echo alltools_icon_svg('grid',16); ?></span><div><span>Total Requests</span><strong id="sp-req">—</strong><em>Measured after test</em></div></div>
            <div class="at-mini-stat at-mini-stat-pad"><strong>Snapshot Scope</strong>
                <ul class="at-recs">
                    <li><?php echo alltools_icon_svg('check',12); ?> Server-side page request</li>
                    <li><?php echo alltools_icon_svg('check',12); ?> Discoverable resource inventory</li>
                    <li><?php echo alltools_icon_svg('check',12); ?> Estimated transfer size</li>
                </ul>
            </div>
        </div>
        <div class="at-speed-subpage-links" style="margin-top:20px;" id="sp-subpage-links">
            <a id="sp-link-report" href="#" class="at-speed-subpage-btn"><?php echo alltools_icon_svg('grid',16); ?> View Full Report</a>
            <a id="sp-link-opps" href="#" class="at-speed-subpage-btn"><?php echo alltools_icon_svg('check',16); ?> View Opportunities</a>
            <a id="sp-link-waterfall" href="#" class="at-speed-subpage-btn"><?php echo alltools_icon_svg('arrow',16); ?> View Resource Inventory</a>
        </div>
    </div>
</div>
<?php }

/* ================================================================
   HTTP STATUS CHECKER (Image 4)
   ================================================================ */
function alltools_tool_http_status_checker() { ?>
<div class="at-card">
    <div class="at-field">
        <label for="hs-url">Website URL</label>
        <div class="at-input-with-btn">
            <span class="at-input-icon"><?php echo alltools_icon_svg('link', 16); ?></span>
            <input type="url" id="hs-url" class="at-input at-input-with-icon" placeholder="https://example.com">
            <button id="hs-check" class="at-btn at-btn-primary">
                <?php echo alltools_icon_svg('search', 16); ?> Check Status
            </button>
        </div>
    </div>

    <div id="hs-result" class="at-tool-grid at-tool-grid-2" style="display:none;margin-top:20px;">
        <div>
            <h3>Final Status</h3>
            <div class="at-status-big" id="hs-status-big">200 OK</div>
            <p id="hs-status-msg">The request was successful.</p>
            <div class="at-output-list" style="margin-top:12px;">
                <div class="at-output-row"><div class="at-output-icon" style="background:#DBEAFE;color:#2563EB;"><?php echo alltools_icon_svg('link',14); ?></div><div class="at-output-lbl">URL</div><div class="at-output-val" id="hs-url-out" style="font-size:11px;">—</div></div>
                <div class="at-output-row"><div class="at-output-icon" style="background:#EDE9FE;color:#8B5CF6;"><?php echo alltools_icon_svg('image',14); ?></div><div class="at-output-lbl">Content Type</div><div class="at-output-val" id="hs-ct">—</div></div>
                <div class="at-output-row"><div class="at-output-icon" style="background:#D1FAE5;color:#10B981;"><?php echo alltools_icon_svg('shield',14); ?></div><div class="at-output-lbl">Server</div><div class="at-output-val" id="hs-srv">—</div></div>
                <div class="at-output-row"><div class="at-output-icon" style="background:#FED7AA;color:#F97316;"><?php echo alltools_icon_svg('calendar',14); ?></div><div class="at-output-lbl">Response Time</div><div class="at-output-val" id="hs-time">—</div></div>
            </div>
            <span class="at-badge at-badge-success" id="hs-success-badge" style="margin-top:12px;">✓ Success</span>
        </div>
        <div>
            <h3>Response Headers</h3>
            <pre class="at-code-block at-code-block-headers" id="hs-headers">Run a check to view headers.</pre>
        </div>
    </div>

    <div class="at-section-block" style="margin-top:32px;">
        <h3>Common Status Codes</h3>
        <div class="at-grid-4">
            <div class="at-status-card at-sc-green"><strong>200</strong><span>200 OK</span><p>The request was successful and the server returned the requested content.</p></div>
            <div class="at-status-card at-sc-blue"><strong>301</strong><span>301 Redirect</span><p>The resource has permanently moved to a new URL.</p></div>
            <div class="at-status-card at-sc-orange"><strong>404</strong><span>404 Not Found</span><p>The requested resource could not be found on the server.</p></div>
            <div class="at-status-card at-sc-red"><strong>500</strong><span>500 Server Error</span><p>The server encountered an unexpected condition.</p></div>
        </div>
    </div>
</div>
<?php }

/* ================================================================
   SSL CHECKER (Image 5)
   ================================================================ */
function alltools_tool_ssl_checker() { ?>
<div class="at-card">
    <div class="at-field">
        <label for="ssl-url">Domain or Website URL</label>
        <div class="at-input-with-btn">
            <span class="at-input-icon"><?php echo alltools_icon_svg('lock', 16); ?></span>
            <input type="text" id="ssl-url" class="at-input at-input-with-icon" placeholder="https://example.com">
            <button id="ssl-check" class="at-btn at-btn-primary">
                <?php echo alltools_icon_svg('search', 16); ?> Check SSL
            </button>
        </div>
        <small class="at-help">Example: google.com, https://example.com</small>
    </div>

    <div id="ssl-result" style="display:none;margin-top:20px;">
        <h3>SSL Certificate Results</h3>
        <div class="at-ssl-grid">
            <div class="at-card at-card-soft at-card-pad-md">
                <small class="at-help">Certificate Status</small>
                <div class="at-ssl-status">
                    <span class="at-ssl-check-icon"><?php echo alltools_icon_svg('check',20); ?></span>
                    <strong id="ssl-status">Valid</strong>
                </div>
                <p class="at-help" id="ssl-status-desc">This certificate is valid and trusted.</p>
            </div>
            <div class="at-card at-card-soft at-card-pad-md">
                <small class="at-help">Valid From</small>
                <div class="at-ssl-date"><?php echo alltools_icon_svg('calendar',16); ?> <strong id="ssl-from">—</strong></div>
                <small id="ssl-from-time" class="at-help">—</small>
            </div>
            <div class="at-card at-card-soft at-card-pad-md">
                <small class="at-help">Expires On</small>
                <div class="at-ssl-date"><?php echo alltools_icon_svg('calendar',16); ?> <strong id="ssl-to">—</strong></div>
                <small id="ssl-to-time" class="at-help">—</small>
            </div>
            <div class="at-card at-card-soft at-card-pad-md">
                <small class="at-help">Days Remaining</small>
                <div class="at-ssl-date"><?php echo alltools_icon_svg('calendar',16); ?> <strong id="ssl-days">—</strong></div>
                <small class="at-help">until expiry</small>
            </div>
        </div>

        <div class="at-grid-2" style="margin-top:16px;">
            <div class="at-card at-card-soft at-card-pad-md">
                <small class="at-help">Issued To (Hostname)</small>
                <div><strong id="ssl-host">—</strong></div>
            </div>
            <div class="at-card at-card-soft at-card-pad-md">
                <small class="at-help">Issued By (Issuer)</small>
                <div class="at-ssl-issuer">
                    <span class="at-ssl-issuer-icon" style="background:#DBEAFE;color:#2563EB;">🏛</span>
                    <div><strong id="ssl-issuer">—</strong><br><small id="ssl-issuer-cn"></small></div>
                </div>
            </div>
            <div class="at-card at-card-soft at-card-pad-md">
                <small class="at-help">Signature Algorithm</small>
                <div class="at-ssl-issuer">
                    <span class="at-ssl-issuer-icon" style="background:#D1FAE5;color:#10B981;"><?php echo alltools_icon_svg('shield',14); ?></span>
                    <strong id="ssl-sig">—</strong>
                </div>
            </div>
        </div>

        <div class="at-grid-2" style="margin-top:16px;">
            <div class="at-card at-card-soft at-card-pad-md">
                <strong>Security Highlights</strong>
                <ul class="at-recs" style="margin-top:12px;">
                    <li><?php echo alltools_icon_svg('check',12); ?> <strong>HTTPS Enabled</strong> — The site is reachable via HTTPS.</li>
                    <li><?php echo alltools_icon_svg('check',12); ?> <strong>Certificate Valid</strong> — The SSL certificate is valid.</li>
                    <li><?php echo alltools_icon_svg('check',12); ?> <strong>Certificate Not Expired</strong> — Within validity period.</li>
                    <li><?php echo alltools_icon_svg('check',12); ?> <strong>Trusted Certificate</strong> — Trusted by major browsers.</li>
                </ul>
            </div>
            <div class="at-card at-card-soft at-card-pad-md">
                <strong>Certificate Chain</strong>
                <ul class="at-ssl-chain" id="ssl-chain" style="margin-top:12px;"></ul>
            </div>
        </div>
    </div>
</div>
<?php }

/* ================================================================
   DNS LOOKUP (Image 6)
   ================================================================ */
function alltools_tool_dns_lookup() { ?>
<div class="at-card">
    <div class="at-field">
        <label for="dns-domain">Domain Name</label>
        <div class="at-input-with-btn">
            <input type="text" id="dns-domain" class="at-input" placeholder="example.com">
            <button id="dns-go" class="at-btn at-btn-primary">
                <?php echo alltools_icon_svg('search', 16); ?> Lookup DNS
            </button>
        </div>
        <small class="at-help">Enter a domain name to retrieve its DNS records.</small>
    </div>

    <div id="dns-result" style="display:none;margin-top:20px;">
        <div class="at-tabs" id="dns-tabs">
            <button class="at-tab is-active" data-dns="all">All Records <span class="at-tab-pill" id="dns-c-all">0</span></button>
            <button class="at-tab" data-dns="A">A <span class="at-tab-pill" id="dns-c-A">0</span></button>
            <button class="at-tab" data-dns="AAAA">AAAA <span class="at-tab-pill" id="dns-c-AAAA">0</span></button>
            <button class="at-tab" data-dns="MX">MX <span class="at-tab-pill" id="dns-c-MX">0</span></button>
            <button class="at-tab" data-dns="TXT">TXT <span class="at-tab-pill" id="dns-c-TXT">0</span></button>
            <button class="at-tab" data-dns="CNAME">CNAME <span class="at-tab-pill" id="dns-c-CNAME">0</span></button>
            <button class="at-tab" data-dns="NS">NS <span class="at-tab-pill" id="dns-c-NS">0</span></button>
        </div>

        <div class="at-table-wrap">
            <table class="at-table at-table-dns">
                <thead><tr><th>Type</th><th>Value</th><th>TTL</th><th>Priority</th></tr></thead>
                <tbody id="dns-tbody"><tr><td colspan="4" class="at-empty">Enter a domain and click Lookup DNS.</td></tr></tbody>
            </table>
        </div>

        <div class="at-tool-grid at-tool-grid-2" style="margin-top:20px;">
            <div class="at-card at-card-soft">
                <h3><?php echo alltools_icon_svg('globe', 18); ?> DNS Summary</h3>
                <div class="at-summary-rows">
                    <div class="at-summary-row"><span>Domain</span><strong id="dns-sum-domain">—</strong></div>
                    <div class="at-summary-row"><span>Nameservers</span><strong id="dns-sum-ns" style="font-size:11px;">—</strong></div>
                    <div class="at-summary-row"><span>Total Records</span><strong id="dns-sum-total">—</strong></div>
                    <div class="at-summary-row"><span>Last Checked</span><strong id="dns-sum-time">—</strong></div>
                </div>
                <span class="at-badge at-badge-success" style="margin-top:12px;">✓ DNS Resolved</span>
            </div>
            <div class="at-card at-card-soft">
                <h3>DNS Record Types</h3>
                <ul class="at-dns-types">
                    <li><span class="at-dns-pill" style="background:#D1FAE5;color:#10B981;">A</span> Maps a domain to an IPv4 address.</li>
                    <li><span class="at-dns-pill" style="background:#EDE9FE;color:#8B5CF6;">AAAA</span> Maps a domain to an IPv6 address.</li>
                    <li><span class="at-dns-pill" style="background:#FED7AA;color:#F97316;">MX</span> Specifies mail servers for the domain.</li>
                    <li><span class="at-dns-pill" style="background:#D1FAE5;color:#10B981;">TXT</span> Stores text information like SPF, verification, etc.</li>
                    <li><span class="at-dns-pill" style="background:#FCE7F3;color:#EC4899;">CNAME</span> Alias of one domain name to another.</li>
                    <li><span class="at-dns-pill" style="background:#DBEAFE;color:#2563EB;">NS</span> Defines the authoritative name servers.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php }

/* ================================================================
   REDIRECT CHECKER (Image 7)
   ================================================================ */
function alltools_tool_redirect_checker() { ?>
<div class="at-card">
    <div class="at-field">
        <label for="rd-url">Website URL</label>
        <div class="at-input-with-btn">
            <span class="at-input-icon"><?php echo alltools_icon_svg('link', 16); ?></span>
            <input type="url" id="rd-url" class="at-input at-input-with-icon" placeholder="https://example.com">
            <button id="rd-check" class="at-btn at-btn-primary">
                <?php echo alltools_icon_svg('redirect', 16); ?> Check Redirects
            </button>
        </div>
        <small class="at-help">Enter a URL to trace its redirect path and final destination.</small>
    </div>

    <div id="rd-result" style="display:none;margin-top:20px;">
        <div class="at-card-head-split">
            <div><h3>Redirect Chain <span class="at-badge at-badge-soft" id="rd-hops-badge">0 hops</span></h3></div>
            <small class="at-help" id="rd-time">Checked just now</small>
        </div>

        <div class="at-redirect-chain" id="rd-chain"></div>

        <div class="at-redirect-summary" style="margin-top:20px;">
            <strong>Redirect Summary</strong>
            <div class="at-grid-4" style="margin-top:12px;">
                <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#D1FAE5;color:#10B981;"><?php echo alltools_icon_svg('check',16); ?></span><div><span>Final Status</span><strong id="rd-final" style="color:#10B981;">—</strong></div></div>
                <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#EDE9FE;color:#8B5CF6;"><?php echo alltools_icon_svg('redirect',16); ?></span><div><span>Redirect Type</span><strong id="rd-type">—</strong></div></div>
                <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#DBEAFE;color:#2563EB;"><?php echo alltools_icon_svg('arrow',16); ?></span><div><span>Total Hops</span><strong id="rd-hops">—</strong></div></div>
                <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#FED7AA;color:#F97316;"><?php echo alltools_icon_svg('calendar',16); ?></span><div><span>Total Time</span><strong id="rd-total-time">—</strong></div></div>
            </div>
        </div>
    </div>
</div>
<?php }

/* ================================================================
   WEBSITE SPEED TEST — FULL REPORT (sub-page)
   ================================================================ */
function alltools_tool_website_speed_test_full_report() {
    $base_url = alltools_tool_url('website-speed-test');
    ?>
<div class="at-card at-speed-bar">
    <div class="at-speed-bar-inner">
        <div class="at-field" style="flex:1;">
            <label>Tested URL</label>
            <div class="at-input-icon-wrap"><span class="at-input-icon"><?php echo alltools_icon_svg('globe',16); ?></span><input type="url" id="spr-url" class="at-input at-input-with-icon" placeholder="https://example.com"></div>
        </div>
        <div class="at-speed-bar-meta"><span>Tested on</span><strong id="spr-date">—</strong></div>
        <div class="at-speed-bar-meta"><span>From</span><strong>Local Server</strong></div>
        <button id="spr-run" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('gauge',16); ?> Run Again</button>
    </div>
</div>

<div class="at-speed-summary-metrics" style="margin-top:24px;">
    <div class="at-speed-sum-score"><svg viewBox="0 0 120 120" width="80" height="80"><circle cx="60" cy="60" r="52" fill="none" stroke="#E2E8F0" stroke-width="10"/><circle id="spr-ring" cx="60" cy="60" r="52" fill="none" stroke="#10B981" stroke-width="10" stroke-dasharray="327" stroke-dashoffset="327" transform="rotate(-90 60 60)" stroke-linecap="round"/></svg><div class="at-speed-sum-score-num" id="spr-score">—</div><small id="spr-rating">—</small></div>
    <div class="at-speed-sum-metric"><span>Server Fetch Time</span><strong id="spr-load">—</strong><em id="spr-load-r">Pending</em></div>
    <div class="at-speed-sum-metric"><span>First Contentful Paint</span><strong id="spr-fcp">—</strong><em id="spr-fcp-r">Pending</em></div>
    <div class="at-speed-sum-metric"><span>Largest Contentful Paint</span><strong id="spr-lcp">—</strong><em id="spr-lcp-r">Pending</em></div>
    <div class="at-speed-sum-metric"><span>Total Blocking Time</span><strong id="spr-tbt">—</strong><em id="spr-tbt-r">Pending</em></div>
    <div class="at-speed-sum-metric"><span>Cumulative Layout Shift</span><strong id="spr-cls">—</strong><em id="spr-cls-r">Pending</em></div>
    <div class="at-speed-sum-metric"><span>Page Size</span><strong id="spr-size">—</strong><em>Measured after test</em></div>
    <div class="at-speed-sum-metric"><span>Requests</span><strong id="spr-req">—</strong><em>Measured after test</em></div>
</div>

<div class="at-card" style="margin-top:24px;">
    <div class="at-speed-report-tabs">
        <button class="at-tab is-active" data-rtab="overview">Overview</button>
        <button class="at-tab" data-rtab="cwv">Core Web Vitals</button>
        <button class="at-tab" data-rtab="opportunities">Opportunities</button>
        <button class="at-tab" data-rtab="diagnostics">Diagnostics</button>
        <button class="at-tab" data-rtab="waterfall">Resource Inventory</button>
    </div>
    <div class="at-speed-report-actions">
        <button class="at-btn at-btn-outline at-btn-sm" id="spr-download"><?php echo alltools_icon_svg('download',14); ?> Download PDF</button>
        <button class="at-btn at-btn-outline at-btn-sm" id="spr-share"><?php echo alltools_icon_svg('heart',14); ?> Share Report</button>
    </div>

    <!-- Overview Tab -->
    <div class="at-speed-tab-pane" data-pane="overview">
        <div class="at-tool-grid at-tool-grid-2" style="margin-top:20px;">
            <div>
                <h3>Server Snapshot Score</h3>
                <div class="at-speed-overview-score">
                    <svg viewBox="0 0 120 120" width="120" height="120"><circle cx="60" cy="60" r="52" fill="none" stroke="#E2E8F0" stroke-width="10"/><circle id="spr-ring2" cx="60" cy="60" r="52" fill="none" stroke="#10B981" stroke-width="10" stroke-dasharray="327" stroke-dashoffset="327" transform="rotate(-90 60 60)" stroke-linecap="round"/></svg>
                    <div class="at-speed-score-num" id="spr-score2">—</div><div class="at-speed-score-lbl" id="spr-rating2">—</div>
                    <div class="at-badge" style="margin-top:8px;font-size:11px;">Run the test to generate a measured score</div>
                </div>
            </div>
            <div>
                <h3>Test History</h3>
                <div class="at-score-history-chart" id="spr-history-chart">
                    <div class="at-score-history-placeholder">This on-demand tool does not store test history.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Web Vitals Tab -->
    <div class="at-speed-tab-pane" data-pane="cwv" style="display:none;">
        <h3 style="margin-top:20px;">Browser Metrics</h3>
        <div class="at-cwv-grid">
            <div class="at-cwv-item"><div class="at-cwv-label">First Contentful Paint (FCP)</div><div class="at-cwv-val" id="spr-cwv-fcp">—</div><div class="at-cwv-bar-wrap"><div class="at-cwv-bar-track"><div class="at-cwv-fill at-cwv-good" id="spr-cwv-fcp-bar" style="width:0%"></div></div><div class="at-cwv-scale"><span>0s</span><span>1.8s</span><span>3s</span></div></div></div>
            <div class="at-cwv-item"><div class="at-cwv-label">Largest Contentful Paint (LCP)</div><div class="at-cwv-val" id="spr-cwv-lcp">—</div><div class="at-cwv-bar-wrap"><div class="at-cwv-bar-track"><div class="at-cwv-fill at-cwv-good" id="spr-cwv-lcp-bar" style="width:0%"></div></div><div class="at-cwv-scale"><span>0s</span><span>2.5s</span><span>4s</span></div></div></div>
            <div class="at-cwv-item"><div class="at-cwv-label">Total Blocking Time (TBT)</div><div class="at-cwv-val" id="spr-cwv-tbt">—</div><div class="at-cwv-bar-wrap"><div class="at-cwv-bar-track"><div class="at-cwv-fill at-cwv-good" id="spr-cwv-tbt-bar" style="width:0%"></div></div><div class="at-cwv-scale"><span>0ms</span><span>200ms</span><span>500ms</span></div></div></div>
            <div class="at-cwv-item"><div class="at-cwv-label">Cumulative Layout Shift (CLS)</div><div class="at-cwv-val" id="spr-cwv-cls">—</div><div class="at-cwv-bar-wrap"><div class="at-cwv-bar-track"><div class="at-cwv-fill at-cwv-good" id="spr-cwv-cls-bar" style="width:0%"></div></div><div class="at-cwv-scale"><span>0</span><span>0.1</span><span>0.25</span></div></div></div>
            <div class="at-cwv-item"><div class="at-cwv-label">Server Response Time (TTFB)</div><div class="at-cwv-val" id="spr-cwv-ttfb">—</div><div class="at-cwv-bar-wrap"><div class="at-cwv-bar-track"><div class="at-cwv-fill at-cwv-good" id="spr-cwv-ttfb-bar" style="width:0%"></div></div><div class="at-cwv-scale"><span>0ms</span><span>800ms</span><span>1800ms</span></div></div></div>
        </div>
        <div class="at-badge" style="margin-top:16px;">FCP, LCP, TBT and CLS require a real browser or Lighthouse run and are not inferred by this server snapshot.</div>
    </div>

    <!-- Opportunities Tab -->
    <div class="at-speed-tab-pane" data-pane="opportunities" style="display:none;">
        <div style="margin-top:20px;">
            <a href="<?php echo esc_url( alltools_tool_url('website-speed-test') ); ?>?view=opportunities" class="at-btn at-btn-outline at-btn-sm">View All Opportunities →</a>
        </div>
        <div class="at-table-wrap" style="margin-top:16px;">
            <table class="at-table"><thead><tr><th>Opportunity</th><th>Estimated Savings</th><th>Priority</th></tr></thead>
            <tbody id="spr-opps-tbody"><tr><td colspan="3" class="at-empty">Run a test to view opportunities.</td></tr></tbody></table>
        </div>
    </div>

    <!-- Diagnostics Tab -->
    <div class="at-speed-tab-pane" data-pane="diagnostics" style="display:none;">
        <div class="at-tool-grid at-tool-grid-2" style="margin-top:20px;">
            <div>
                <h3>Diagnostics Summary</h3>
                <ul class="at-recs" id="spr-diag-list">
                    <li><?php echo alltools_icon_svg('check',12); ?> Run a test to see diagnostics.</li>
                </ul>
            </div>
            <div>
                <h3>Test Environment</h3>
                <div class="at-summary-rows">
                    <div class="at-summary-row"><span>Device</span><strong>Desktop (1920×1080)</strong></div>
                    <div class="at-summary-row"><span>Browser</span><strong>Chrome (Latest)</strong></div>
                    <div class="at-summary-row"><span>Connection</span><strong>Cable (Fast 20/5 Mbps)</strong></div>
                    <div class="at-summary-row"><span>Test Time</span><strong id="spr-env-time">—</strong></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Waterfall Tab -->
    <div class="at-speed-tab-pane" data-pane="waterfall" style="display:none;">
        <div style="margin-top:20px;">
            <a href="<?php echo esc_url( alltools_tool_url('website-speed-test') ); ?>?view=waterfall" class="at-btn at-btn-outline at-btn-sm">View Resource Inventory →</a>
        </div>
        <p class="at-help" style="margin-top:12px;">The inventory lists resources discovered in page markup and a limited number of server-side probes; it is not a browser network waterfall.</p>
    </div>

</div>
<?php }

/* ================================================================
   WEBSITE SPEED TEST — OPPORTUNITIES (sub-page)
   ================================================================ */
function alltools_tool_website_speed_test_opportunities() {
    $base_url = alltools_tool_url('website-speed-test');
    ?>
<div class="at-card at-speed-bar">
    <div class="at-speed-bar-inner">
        <div class="at-field" style="flex:1;"><label>Tested URL</label><div class="at-input-icon-wrap"><span class="at-input-icon"><?php echo alltools_icon_svg('globe',16); ?></span><input type="url" id="spo-url" class="at-input at-input-with-icon" placeholder="https://example.com"></div></div>
        <div class="at-speed-bar-meta"><span>Tested on</span><strong id="spo-date">—</strong></div>
        <button id="spo-run" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('gauge',16); ?> Run Again</button>
    </div>
</div>

<div class="at-grid-5" style="margin-top:24px;">
    <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#DBEAFE;color:#2563EB;"><?php echo alltools_icon_svg('download',16); ?></span><div><span>Total Estimated Savings</span><strong id="spo-savings">1.12 MB</strong><em>-64%</em></div></div>
    <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#FEE2E2;color:#EF4444;">⚠</span><div><span>High Priority Issues</span><strong id="spo-high" style="color:#EF4444;">3</strong><em>Require immediate attention</em></div></div>
    <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#FEF3C7;color:#F59E0B;">⚠</span><div><span>Medium Priority Issues</span><strong id="spo-med" style="color:#F59E0B;">3</strong><em>Should be addressed soon</em></div></div>
    <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#D1FAE5;color:#10B981;"><?php echo alltools_icon_svg('check',16); ?></span><div><span>Low Priority Issues</span><strong id="spo-low" style="color:#10B981;">2</strong><em>Nice to have improvements</em></div></div>
    <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#EDE9FE;color:#8B5CF6;"><?php echo alltools_icon_svg('gauge',16); ?></span><div><span>Estimated Load Impact</span><strong id="spo-impact" style="color:#10B981;">-1.24 s</strong><em>Potential improvement</em></div></div>
</div>

<div class="at-card" style="margin-top:24px;">
    <div class="at-card-head at-card-head-split">
        <h3>Optimization Opportunities (<span id="spo-count">8</span>)</h3>
        <div style="display:flex;gap:8px;align-items:center;">
            <select id="spo-filter" class="at-input" style="width:140px;padding:6px 10px;font-size:13px;">
                <option value="">All Priorities</option>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>
            <button class="at-btn at-btn-outline at-btn-sm" id="spo-export"><?php echo alltools_icon_svg('download',14); ?> Export CSV</button>
        </div>
    </div>
    <div class="at-table-wrap">
        <table class="at-table at-table-opps">
            <thead><tr><th></th><th>Opportunity</th><th>Estimated Savings</th><th>Priority</th><th>Affected Pages / Resources</th><th>Issue Description</th><th>Recommended Fix</th><th>Action</th></tr></thead>
            <tbody id="spo-tbody">
                <tr><td colspan="8" class="at-empty">Run a speed test to see optimization opportunities.</td></tr>
            </tbody>
        </table>
    </div>
    <div class="at-table-footer">
        <span id="spo-showing">Showing 0 opportunities</span>
        <div class="at-pagination" id="spo-pagination"></div>
        <div style="display:flex;align-items:center;gap:8px;"><span class="at-help">Rows per page:</span><select class="at-input" style="width:60px;padding:4px 6px;font-size:12px;" id="spo-rows"><option>10</option><option>25</option><option>50</option></select></div>
    </div>
</div>
<?php }

/* ================================================================
   WEBSITE SPEED TEST — WATERFALL (sub-page)
   ================================================================ */
function alltools_tool_website_speed_test_waterfall() {
    $base_url = alltools_tool_url('website-speed-test');
    ?>
<div class="at-card at-speed-bar">
    <div class="at-speed-bar-inner">
        <div class="at-field" style="flex:1;"><label>Tested URL</label><div class="at-input-icon-wrap"><span class="at-input-icon"><?php echo alltools_icon_svg('globe',16); ?></span><input type="url" id="spw-url" class="at-input at-input-with-icon" placeholder="https://example.com"></div></div>
        <div class="at-speed-bar-meta"><span>Tested on</span><strong id="spw-date">—</strong></div>
        <button id="spw-run" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('gauge',16); ?> Run Again</button>
    </div>
</div>

<div class="at-grid-5" style="margin-top:24px;">
    <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#D1FAE5;color:#10B981;"><?php echo alltools_icon_svg('grid',16); ?></span><div><span>Discovered Resources</span><strong id="spw-requests">—</strong><em id="spw-req-r">Inventory</em></div></div>
    <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#DBEAFE;color:#2563EB;"><?php echo alltools_icon_svg('download',16); ?></span><div><span>Estimated Transfer Size</span><strong id="spw-size">—</strong><em id="spw-size-r">Probed subset</em></div></div>
    <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#FED7AA;color:#F97316;"><?php echo alltools_icon_svg('pulse',16); ?></span><div><span>Server Fetch Time</span><strong id="spw-load">—</strong><em id="spw-load-r">This request</em></div></div>
    <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#EDE9FE;color:#8B5CF6;"><?php echo alltools_icon_svg('globe',16); ?></span><div><span>Third-Party Requests</span><strong id="spw-third">—</strong><em>External</em></div></div>
    <div class="at-mini-stat"><span class="at-mini-stat-icon" style="background:#FEE2E2;color:#EF4444;">⚠</span><div><span>Slowest Request</span><strong id="spw-slowest">—</strong><em id="spw-slowest-name" style="font-size:10px;">—</em></div></div>
</div>

<div class="at-card" style="margin-top:24px;">
    <div class="at-speed-waterfall-tabs">
        <div class="at-tabs" id="spw-type-tabs">
            <button class="at-tab is-active" data-wtype="all">All <span class="at-tab-pill" id="spw-c-all">0</span></button>
            <button class="at-tab" data-wtype="document">Document <span class="at-tab-pill" id="spw-c-doc">0</span></button>
            <button class="at-tab" data-wtype="script">Scripts <span class="at-tab-pill" id="spw-c-js">0</span></button>
            <button class="at-tab" data-wtype="stylesheet">Stylesheets <span class="at-tab-pill" id="spw-c-css">0</span></button>
            <button class="at-tab" data-wtype="image">Images <span class="at-tab-pill" id="spw-c-img">0</span></button>
            <button class="at-tab" data-wtype="font">Fonts <span class="at-tab-pill" id="spw-c-font">0</span></button>
            <button class="at-tab" data-wtype="third">Third-Party <span class="at-tab-pill" id="spw-c-third">0</span></button>
        </div>
        <div style="display:flex;gap:8px;">
            <button class="at-btn at-btn-outline at-btn-sm" id="spw-export"><?php echo alltools_icon_svg('download',14); ?> Export</button>
            <button class="at-btn at-btn-outline at-btn-sm" id="spw-share"><?php echo alltools_icon_svg('heart',14); ?> Share</button>
        </div>
    </div>

    <div class="at-table-wrap">
        <table class="at-table at-table-waterfall">
            <thead>
                <tr>
                    <th style="width:30%">URL / Resource</th>
                    <th>Status</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Probe Order</th>
                    <th>Probe Duration</th>
                    <th>Browser Blocking</th>
                    <th style="width:20%">Coverage</th>
                </tr>
            </thead>
            <tbody id="spw-tbody">
                <tr><td colspan="8" class="at-empty">Run a server snapshot to build the resource inventory.</td></tr>
            </tbody>
        </table>
    </div>
    <div class="at-table-footer">
        <span id="spw-showing">Showing 0 requests</span>
        <div class="at-pagination" id="spw-pagination"></div>
    </div>
</div>

<div class="at-tool-grid at-tool-grid-2" style="margin-top:24px;">
    <div class="at-card">
        <h3>Key Insights</h3>
        <div id="spw-insights" class="at-insights-grid">
            <div class="at-insight-item"><span style="color:#F97316;">⏱</span><div><strong>Render-Blocking Resources</strong><span id="spw-render-blocking">—</span><a href="#" class="at-link" style="font-size:12px;">View Details →</a></div></div>
            <div class="at-insight-item"><span style="color:#2563EB;">📦</span><div><strong>Largest Resources</strong><span id="spw-largest-res">—</span><a href="#" class="at-link" style="font-size:12px;">View Details →</a></div></div>
            <div class="at-insight-item"><span style="color:#EC4899;">🔄</span><div><strong>Duplicate Requests</strong><span id="spw-duplicates">—</span><a href="#" class="at-link" style="font-size:12px;">View Details →</a></div></div>
        </div>
    </div>
    <div class="at-card">
        <h3>Inventory Scope</h3>
        <div class="at-wf-legend">
            <div class="at-wf-legend-item"><span style="background:#10B981;"></span>Main document is fetched by the server</div>
            <div class="at-wf-legend-item"><span style="background:#3B82F6;"></span>Resources are discovered from page markup</div>
            <div class="at-wf-legend-item"><span style="background:#F59E0B;"></span>Only a limited subset is individually probed</div>
            <div class="at-wf-legend-item"><span style="background:#94A3B8;"></span>Browser timing and blocking are not measured</div>
        </div>
    </div>
</div>
<?php }

function alltools_tool_pdf_compressor() { ?>
<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h3>Upload PDF</h3>
        <label class="at-dropzone" for="ufx-pdf-compress-file">
            <span class="at-dropzone-icon"><?php echo alltools_icon_svg('upload',32); ?></span>
            <strong>Choose a PDF file</strong>
            <span>Processed in your browser</span>
        </label>
        <input type="file" id="ufx-pdf-compress-file" accept="application/pdf" hidden>
        <div class="at-field" style="margin-top:16px;">
            <label>Compression mode</label>
            <select id="ufx-pdf-compress-mode" class="at-input">
                <option value="safe">Safe optimize</option>
                <option value="strong">Strong optimize</option>
            </select>
        </div>
        <button id="ufx-pdf-compress-btn" class="at-btn at-btn-primary" style="margin-top:14px;"><?php echo alltools_icon_svg('download',16); ?> Compress PDF</button>
    </div>
    <div class="at-card">
        <h3>Result</h3>
        <div class="at-output-row"><div class="at-output-lbl">Original Size</div><div class="at-output-val" id="ufx-pdfc-original">—</div></div>
        <div class="at-output-row"><div class="at-output-lbl">New Size</div><div class="at-output-val" id="ufx-pdfc-new">—</div></div>
        <div class="at-output-row"><div class="at-output-lbl">Saved</div><div class="at-output-val" id="ufx-pdfc-saved">—</div></div>
        <p class="at-help">Best results depend on the PDF structure. Heavy scanned PDFs may need server-side compression for bigger reduction.</p>
    </div>
</div>
<?php }

function alltools_tool_merge_pdf() { ?>
<div class="at-card">
    <h3>Merge PDF Files</h3>
    <label class="at-dropzone" for="ufx-merge-pdf-files">
        <span class="at-dropzone-icon at-dropzone-icon-blue"><?php echo alltools_icon_svg('upload',32); ?></span>
        <strong>Select multiple PDF files</strong>
        <span>They will be merged in the same order</span>
    </label>
    <input type="file" id="ufx-merge-pdf-files" accept="application/pdf" multiple hidden>
    <div id="ufx-merge-list" class="at-file-list" style="margin-top:16px;"></div>
    <button id="ufx-merge-pdf-btn" class="at-btn at-btn-primary" style="margin-top:16px;"><?php echo alltools_icon_svg('duplicate',16); ?> Merge PDFs</button>
</div>
<?php }

function alltools_tool_split_pdf() { ?>
<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h3>Split PDF</h3>
        <label class="at-dropzone" for="ufx-split-pdf-file">
            <span class="at-dropzone-icon"><?php echo alltools_icon_svg('upload',32); ?></span>
            <strong>Choose a PDF file</strong>
            <span>Extract selected pages</span>
        </label>
        <input type="file" id="ufx-split-pdf-file" accept="application/pdf" hidden>
        <div class="at-field" style="margin-top:16px;">
            <label>Page range</label>
            <input type="text" id="ufx-split-range" class="at-input" placeholder="Example: 1,3-5,8">
            <p class="at-help">Leave blank to extract the first page only.</p>
        </div>
        <button id="ufx-split-pdf-btn" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('scissors',16); ?> Split PDF</button>
    </div>
    <div class="at-card">
        <h3>File Info</h3>
        <div class="at-output-row"><div class="at-output-lbl">Pages</div><div class="at-output-val" id="ufx-split-pages">—</div></div>
        <div class="at-output-row"><div class="at-output-lbl">Selected</div><div class="at-output-val" id="ufx-split-selected">—</div></div>
    </div>
</div>
<?php }

function alltools_tool_jpg_to_pdf() { ?>
<div class="at-card">
    <h3>JPG to PDF Converter</h3>
    <label class="at-dropzone" for="ufx-jpg-pdf-files">
        <span class="at-dropzone-icon at-dropzone-icon-blue"><?php echo alltools_icon_svg('image',32); ?></span>
        <strong>Select JPG images</strong>
        <span>Multiple images become separate PDF pages</span>
    </label>
    <input type="file" id="ufx-jpg-pdf-files" accept="image/jpeg,image/jpg,image/png,image/webp" multiple hidden>
    <div class="at-tool-grid at-tool-grid-3" style="margin-top:16px;">
        <div class="at-field"><label>Page Size</label><select id="ufx-jpg-page-size" class="at-input"><option value="a4">A4</option><option value="letter">Letter</option><option value="fit">Fit image</option></select></div>
        <div class="at-field"><label>Orientation</label><select id="ufx-jpg-orientation" class="at-input"><option value="portrait">Portrait</option><option value="landscape">Landscape</option></select></div>
        <div class="at-field"><label>Margin</label><input type="number" id="ufx-jpg-margin" class="at-input" value="10" min="0" max="40"></div>
    </div>
    <div id="ufx-jpg-list" class="at-file-list" style="margin-top:16px;"></div>
    <button id="ufx-jpg-pdf-btn" class="at-btn at-btn-primary" style="margin-top:16px;"><?php echo alltools_icon_svg('download',16); ?> Convert to PDF</button>
</div>
<?php }

function alltools_tool_image_compressor() { ?>
<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h3>Compress Image</h3>
        <label class="at-dropzone" for="ufx-img-compress-file">
            <span class="at-dropzone-icon"><?php echo alltools_icon_svg('image',32); ?></span>
            <strong>Choose an image</strong>
            <span>JPG, PNG, or WebP</span>
        </label>
        <input type="file" id="ufx-img-compress-file" accept="image/*" hidden>
        <div class="at-field" style="margin-top:16px;"><label>Quality: <span id="ufx-img-quality-val">75%</span></label><input type="range" id="ufx-img-quality" min="10" max="100" value="75" class="at-range"></div>
        <div class="at-field"><label>Output Format</label><select id="ufx-img-format" class="at-input"><option value="image/jpeg">JPG</option><option value="image/webp">WebP</option><option value="image/png">PNG</option></select></div>
        <button id="ufx-img-compress-btn" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('download',16); ?> Compress Image</button>
    </div>
    <div class="at-card">
        <h3>Preview & Result</h3>
        <canvas id="ufx-img-preview" style="max-width:100%;border-radius:16px;background:#f8fafc;"></canvas>
        <div class="at-output-row"><div class="at-output-lbl">Original</div><div class="at-output-val" id="ufx-img-original">—</div></div>
        <div class="at-output-row"><div class="at-output-lbl">Compressed</div><div class="at-output-val" id="ufx-img-new">—</div></div>
    </div>
</div>
<?php }

function alltools_tool_color_picker_from_image() { ?>
<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h3>Upload Image</h3>
        <label class="at-dropzone" for="ufx-color-image">
            <span class="at-dropzone-icon at-dropzone-icon-blue"><?php echo alltools_icon_svg('upload',32); ?></span>
            <strong>Choose an image</strong>
            <span>Click on the image to pick a color</span>
        </label>
        <input type="file" id="ufx-color-image" accept="image/*" hidden>
        <canvas id="ufx-color-canvas" style="max-width:100%;margin-top:16px;border-radius:16px;background:#f8fafc;cursor:crosshair;"></canvas>
    </div>
    <div class="at-card">
        <h3>Picked Color</h3>
        <div id="ufx-color-swatch" style="height:110px;border-radius:18px;background:#e2e8f0;border:1px solid #cbd5e1;margin-bottom:16px;"></div>
        <div class="at-output-row"><div class="at-output-lbl">HEX</div><div class="at-output-val" id="ufx-color-hex">—</div></div>
        <div class="at-output-row"><div class="at-output-lbl">RGB</div><div class="at-output-val" id="ufx-color-rgb">—</div></div>
        <div class="at-output-row"><div class="at-output-lbl">HSL</div><div class="at-output-val" id="ufx-color-hsl">—</div></div>
        <button id="ufx-color-copy" class="at-btn at-btn-outline"><?php echo alltools_icon_svg('copy',16); ?> Copy HEX</button>
    </div>
</div>
<?php }

function alltools_tool_json_formatter() { ?>
<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h3>Input JSON</h3>
        <textarea id="ufx-json-input" class="at-textarea" rows="16" placeholder='{"name":"Uptime Fixer","tools":27}'></textarea>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;">
            <button id="ufx-json-format" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('code',16); ?> Format</button>
            <button id="ufx-json-minify" class="at-btn at-btn-outline">Minify</button>
            <button id="ufx-json-sort" class="at-btn at-btn-outline">Sort Keys</button>
            <button id="ufx-json-clear" class="at-btn at-btn-outline">Clear</button>
        </div>
    </div>
    <div class="at-card">
        <h3>Formatted JSON</h3>
        <textarea id="ufx-json-output" class="at-textarea" rows="16" readonly></textarea>
        <div class="at-output-row"><div class="at-output-lbl">Status</div><div class="at-output-val" id="ufx-json-status">Waiting</div></div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;">
            <button id="ufx-json-copy" class="at-btn at-btn-outline"><?php echo alltools_icon_svg('copy',16); ?> Copy</button>
            <button id="ufx-json-download" class="at-btn at-btn-outline"><?php echo alltools_icon_svg('download',16); ?> Download</button>
        </div>
    </div>
</div>
<?php }

/* ================================================================
   ADDITIONAL WEBSITE TOOLS
   ================================================================ */
function alltools_tool_domain_expiry_checker() { ?>
<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h3>Check Domain Expiry</h3>
        <div class="at-field"><label>Domain or URL</label><div class="at-input-icon-wrap"><span class="at-input-icon"><?php echo alltools_icon_svg('globe',16); ?></span><input type="text" id="ufx-domain-expiry-input" class="at-input at-input-with-icon" placeholder="example.com"></div></div>
        <button id="ufx-domain-expiry-btn" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('calendar',16); ?> Check Domain</button>
        <p class="at-help">Uses public RDAP data where available. Some registries hide or omit expiry details.</p>
    </div>
    <div class="at-card" id="ufx-domain-expiry-result" style="display:none;">
        <h3>Domain Details</h3>
        <div class="at-output-row"><div class="at-output-lbl">Domain</div><div class="at-output-val" id="ufx-de-domain">—</div></div>
        <div class="at-output-row"><div class="at-output-lbl">Status</div><div class="at-output-val" id="ufx-de-status">—</div></div>
        <div class="at-output-row"><div class="at-output-lbl">Created</div><div class="at-output-val" id="ufx-de-created">—</div></div>
        <div class="at-output-row"><div class="at-output-lbl">Updated</div><div class="at-output-val" id="ufx-de-updated">—</div></div>
        <div class="at-output-row"><div class="at-output-lbl">Expires</div><div class="at-output-val" id="ufx-de-expires">—</div></div>
        <div class="at-output-row"><div class="at-output-lbl">Days Left</div><div class="at-output-val" id="ufx-de-days">—</div></div>
        <div class="at-output-row"><div class="at-output-lbl">Registrar</div><div class="at-output-val" id="ufx-de-registrar">—</div></div>
        <div class="at-output-row"><div class="at-output-lbl">Nameservers</div><div class="at-output-val" id="ufx-de-ns">—</div></div>
    </div>
</div>
<?php }

function alltools_tool_broken_link_checker() { ?>
<div class="at-card">
    <h3>Broken Link Checker</h3>
    <div class="at-tool-grid at-tool-grid-3">
        <div class="at-field" style="grid-column:span 2;"><label>Page URL</label><div class="at-input-icon-wrap"><span class="at-input-icon"><?php echo alltools_icon_svg('link',16); ?></span><input type="url" id="ufx-bl-url" class="at-input at-input-with-icon" placeholder="https://example.com/page"></div></div>
        <div class="at-field"><label>Links to check</label><select id="ufx-bl-limit" class="at-input"><option value="10">10</option><option value="25" selected>25</option><option value="50">50</option></select></div>
    </div>
    <button id="ufx-bl-btn" class="at-btn at-btn-primary" style="margin-top:14px;"><?php echo alltools_icon_svg('link',16); ?> Scan Links</button>
    <div id="ufx-bl-summary" class="at-tool-grid at-tool-grid-3" style="display:none;margin-top:18px;">
        <div class="at-mini-stat"><span class="at-mini-stat-icon"><?php echo alltools_icon_svg('grid',16); ?></span><div><span>Checked</span><strong id="ufx-bl-checked">—</strong></div></div>
        <div class="at-mini-stat"><span class="at-mini-stat-icon"><?php echo alltools_icon_svg('check',16); ?></span><div><span>OK</span><strong id="ufx-bl-ok">—</strong></div></div>
        <div class="at-mini-stat"><span class="at-mini-stat-icon">⚠</span><div><span>Broken</span><strong id="ufx-bl-broken">—</strong></div></div>
    </div>
    <div class="at-table-wrap" style="margin-top:18px;"><table class="at-table"><thead><tr><th>URL</th><th>Status</th><th>Message</th><th>Time</th></tr></thead><tbody id="ufx-bl-tbody"><tr><td colspan="4" class="at-empty">Run a scan to see link status.</td></tr></tbody></table></div>
</div>
<?php }

function alltools_tool_robots_txt_generator() { ?>
<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h3>Robots.txt Settings</h3>
        <div class="at-field"><label>Website URL</label><input type="url" id="ufx-robots-site" class="at-input" placeholder="https://example.com"></div>
        <div class="at-field"><label>Sitemap URL</label><input type="url" id="ufx-robots-sitemap" class="at-input" placeholder="https://example.com/sitemap.xml"></div>
        <div class="at-field"><label>Rule type</label><select id="ufx-robots-rule" class="at-input"><option value="allow">Allow all bots</option><option value="block-admin">Block admin/private paths</option><option value="block-all">Block all bots</option><option value="custom">Custom disallow paths</option></select></div>
        <div class="at-field"><label>Custom disallow paths, one per line</label><textarea id="ufx-robots-paths" class="at-textarea" rows="6" placeholder="/private/&#10;/tmp/"></textarea></div>
        <button id="ufx-robots-generate" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('code',16); ?> Generate</button>
    </div>
    <div class="at-card">
        <h3>Generated Robots.txt</h3>
        <textarea id="ufx-robots-output" class="at-textarea" rows="16" readonly></textarea>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;"><button id="ufx-robots-copy" class="at-btn at-btn-outline"><?php echo alltools_icon_svg('copy',16); ?> Copy</button><button id="ufx-robots-download" class="at-btn at-btn-outline"><?php echo alltools_icon_svg('download',16); ?> Download</button></div>
    </div>
</div>
<?php }

function alltools_tool_xml_sitemap_generator() { ?>
<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h3>Generate XML Sitemap</h3>
        <div class="at-field"><label>URLs, one per line</label><textarea id="ufx-sitemap-urls" class="at-textarea" rows="12" placeholder="https://example.com/&#10;https://example.com/about/"></textarea></div>
        <div class="at-tool-grid at-tool-grid-2"><div class="at-field"><label>Change Frequency</label><select id="ufx-sitemap-freq" class="at-input"><option>daily</option><option selected>weekly</option><option>monthly</option><option>yearly</option></select></div><div class="at-field"><label>Priority</label><input type="number" id="ufx-sitemap-priority" class="at-input" value="0.8" min="0.1" max="1" step="0.1"></div></div>
        <button id="ufx-sitemap-generate" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('globe',16); ?> Generate Sitemap</button>
    </div>
    <div class="at-card">
        <h3>XML Output</h3>
        <textarea id="ufx-sitemap-output" class="at-textarea" rows="16" readonly></textarea>
        <div class="at-output-row"><div class="at-output-lbl">URL Count</div><div class="at-output-val" id="ufx-sitemap-count">0</div></div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;"><button id="ufx-sitemap-copy" class="at-btn at-btn-outline"><?php echo alltools_icon_svg('copy',16); ?> Copy</button><button id="ufx-sitemap-download" class="at-btn at-btn-outline"><?php echo alltools_icon_svg('download',16); ?> Download</button></div>
    </div>
</div>
<?php }

/* ================================================================
   ADDITIONAL PDF / IMAGE TOOLS
   ================================================================ */
function alltools_tool_pdf_to_jpg() { ?>
<div class="at-card">
    <h3>PDF to JPG Converter</h3>
    <label class="at-dropzone" for="ufx-pdf-jpg-file"><span class="at-dropzone-icon"><?php echo alltools_icon_svg('upload',32); ?></span><strong>Choose a PDF file</strong><span>Convert selected pages to JPG</span></label>
    <input type="file" id="ufx-pdf-jpg-file" accept="application/pdf" hidden>
    <div class="at-tool-grid at-tool-grid-3" style="margin-top:16px;"><div class="at-field"><label>Page range</label><input type="text" id="ufx-pdf-jpg-range" class="at-input" placeholder="1,3-5 or blank for all"></div><div class="at-field"><label>Quality</label><input type="range" id="ufx-pdf-jpg-quality" min="50" max="100" value="90" class="at-range"></div><div class="at-field"><label>Scale</label><select id="ufx-pdf-jpg-scale" class="at-input"><option value="1">1x</option><option value="1.5" selected>1.5x</option><option value="2">2x</option></select></div></div>
    <button id="ufx-pdf-jpg-btn" class="at-btn at-btn-primary" style="margin-top:16px;"><?php echo alltools_icon_svg('image',16); ?> Convert Pages</button>
    <div id="ufx-pdf-jpg-results" class="at-file-list" style="margin-top:16px;"></div>
</div>
<?php }

function alltools_tool_invoice_generator() { ?>
<style>
.ufx-invoice-premium{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(360px,.85fr);gap:24px;align-items:start}
.ufx-invoice-hero{padding:22px;border-radius:22px;background:linear-gradient(135deg,#0f172a,#2563eb);color:#fff;margin-bottom:18px;box-shadow:0 18px 45px rgba(37,99,235,.22)}
.ufx-invoice-hero h3{margin:0 0 8px;font-size:24px;color:#fff}
.ufx-invoice-hero p{margin:0;color:rgba(255,255,255,.82)}
.ufx-premium-card{border:1px solid #e5e7eb;border-radius:22px;background:#fff;box-shadow:0 18px 55px rgba(15,23,42,.08);overflow:hidden}
.ufx-premium-card-inner{padding:22px}
.ufx-premium-section-title{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:20px 0 12px}
.ufx-premium-section-title h4{margin:0;font-size:15px;letter-spacing:.02em;text-transform:uppercase;color:#334155}
.ufx-inv-logo-box{display:flex;align-items:center;gap:14px;padding:14px;border:1px dashed #cbd5e1;border-radius:16px;background:#f8fafc}
.ufx-inv-logo-preview{width:76px;height:52px;border-radius:12px;background:#fff;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;overflow:hidden;color:#94a3b8;font-size:12px}
.ufx-inv-logo-preview img{width:100%;height:100%;object-fit:contain}
.ufx-inv-table-wrap{overflow:auto;border:1px solid #e5e7eb;border-radius:18px;background:#fff}
.ufx-inv-table{width:100%;border-collapse:separate;border-spacing:0;min-width:820px}
.ufx-inv-table th{background:#f8fafc;color:#475569;font-size:12px;text-transform:uppercase;letter-spacing:.04em;padding:12px;border-bottom:1px solid #e5e7eb;text-align:left}
.ufx-inv-table td{padding:10px;border-bottom:1px solid #eef2f7;vertical-align:middle}
.ufx-inv-table tbody tr:hover{background:#f8fafc}
.ufx-inv-table .at-input{min-height:38px;border-radius:10px}
.ufx-inv-remove{padding:8px 10px!important;font-size:12px!important}
.ufx-invoice-preview{background:#f8fafc;padding:16px;border-radius:22px;border:1px solid #e5e7eb;position:sticky;top:18px}
.ufx-invoice-paper{background:#fff;border-radius:16px;box-shadow:0 20px 55px rgba(15,23,42,.13);overflow:hidden;border:1px solid #e5e7eb}
.ufx-preview-topbar{height:8px;background:#2563eb}
.ufx-preview-body{padding:24px}
.ufx-preview-head{display:flex;justify-content:space-between;gap:18px;align-items:flex-start}
.ufx-preview-logo{width:110px;min-height:58px;border-radius:12px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;overflow:hidden;color:#64748b;font-size:13px;font-weight:700}
.ufx-preview-logo img{width:100%;height:100%;object-fit:contain}
.ufx-preview-title{text-align:right}
.ufx-preview-title h2{margin:0;font-size:32px;letter-spacing:.08em;color:#0f172a}
.ufx-preview-title p{margin:5px 0 0;color:#64748b;font-size:13px}
.ufx-preview-parties{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-top:28px}
.ufx-preview-box{border:1px solid #e5e7eb;border-radius:14px;padding:14px;background:#fbfdff}
.ufx-preview-box strong{display:block;color:#0f172a;margin-bottom:7px}
.ufx-preview-box span{white-space:pre-line;color:#475569;font-size:13px;line-height:1.45}
.ufx-preview-items{width:100%;border-collapse:collapse;margin-top:22px;font-size:12px}
.ufx-preview-items th{background:#0f172a;color:#fff;padding:10px;text-align:left}
.ufx-preview-items td{padding:10px;border-bottom:1px solid #e5e7eb;color:#334155}
.ufx-preview-items th:last-child,.ufx-preview-items td:last-child{text-align:right}
.ufx-preview-totals{margin:20px 0 0 auto;width:min(100%,260px);border-radius:14px;background:#f8fafc;border:1px solid #e5e7eb;padding:12px}
.ufx-preview-total-row{display:flex;justify-content:space-between;padding:6px 0;color:#475569}
.ufx-preview-total-row.is-final{font-weight:800;color:#0f172a;border-top:1px solid #cbd5e1;margin-top:6px;padding-top:10px;font-size:18px}
.ufx-inv-summary-badges{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:16px}
.ufx-inv-badge{border:1px solid #e5e7eb;border-radius:16px;padding:14px;background:#fff}
.ufx-inv-badge span{display:block;color:#64748b;font-size:12px}
.ufx-inv-badge strong{display:block;margin-top:4px;font-size:20px;color:#0f172a}
@media(max-width:980px){.ufx-invoice-premium{grid-template-columns:1fr}.ufx-invoice-preview{position:static}.ufx-preview-parties{grid-template-columns:1fr}}
</style>
<div class="ufx-invoice-premium">
    <div>
        <div class="ufx-invoice-hero">
            <h3>Premium Invoice Generator</h3>
            <p>Create a clean branded invoice with logo, item rows, discount, tax, notes, live preview and a professional PDF.</p>
        </div>
        <div class="ufx-premium-card">
            <div class="ufx-premium-card-inner">
                <div class="ufx-premium-section-title"><h4>Brand & Template</h4></div>
                <div class="at-tool-grid at-tool-grid-3">
                    <div class="at-field"><label>Template Style</label><select id="ufx-inv-template" class="at-input"><option value="blue">Modern Blue</option><option value="charcoal">Charcoal Premium</option><option value="emerald">Emerald Clean</option></select></div>
                    <div class="at-field"><label>Accent Color</label><input type="color" id="ufx-inv-accent" class="at-input" value="#2563eb"></div>
                    <div class="at-field"><label>Currency Symbol</label><input id="ufx-inv-currency" class="at-input" value="$" placeholder="$"></div>
                </div>
                <div class="ufx-inv-logo-box" style="margin-top:14px;">
                    <div id="ufx-inv-logo-preview" class="ufx-inv-logo-preview">Logo</div>
                    <div style="flex:1;">
                        <label style="font-weight:700;display:block;margin-bottom:6px;">Business Logo</label>
                        <input type="file" id="ufx-inv-logo" accept="image/*" class="at-input">
                        <p class="at-help" style="margin:6px 0 0;">Optional. PNG/JPG/WebP logo will appear in the preview and PDF.</p>
                    </div>
                </div>

                <div class="ufx-premium-section-title"><h4>Business & Client</h4></div>
                <div class="at-tool-grid at-tool-grid-2">
                    <div class="at-field"><label>Business Name</label><input id="ufx-inv-business" class="at-input" placeholder="Your Company"></div>
                    <div class="at-field"><label>Client Name</label><input id="ufx-inv-client" class="at-input" placeholder="Client Name"></div>
                </div>
                <div class="at-tool-grid at-tool-grid-2">
                    <div class="at-field"><label>Business Details</label><textarea id="ufx-inv-business-details" class="at-textarea" rows="4" placeholder="Address&#10;Phone&#10;Email"></textarea></div>
                    <div class="at-field"><label>Client Details</label><textarea id="ufx-inv-client-details" class="at-textarea" rows="4" placeholder="Address&#10;Phone&#10;Email"></textarea></div>
                </div>

                <div class="ufx-premium-section-title"><h4>Invoice Info</h4></div>
                <div class="at-tool-grid at-tool-grid-3">
                    <div class="at-field"><label>Invoice #</label><input id="ufx-inv-number" class="at-input" value="INV-001"></div>
                    <div class="at-field"><label>Issue Date</label><input type="date" id="ufx-inv-date" class="at-input"></div>
                    <div class="at-field"><label>Due Date</label><input type="date" id="ufx-inv-due" class="at-input"></div>
                </div>

                <div class="ufx-premium-section-title"><h4>Invoice Items</h4><button id="ufx-inv-add-item" class="at-btn at-btn-outline at-btn-sm" type="button">+ Add Item</button></div>
                <div class="ufx-inv-table-wrap">
                    <table class="ufx-inv-table">
                        <thead>
                            <tr>
                                <th style="width:30%;">Description</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Discount %</th>
                                <th>Tax %</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="ufx-inv-items-body"></tbody>
                    </table>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;">
                    <button id="ufx-inv-sample" class="at-btn at-btn-outline" type="button">Load Sample</button>
                    <button id="ufx-inv-preview" class="at-btn at-btn-outline" type="button">Refresh Preview</button>
                    <button id="ufx-inv-download" class="at-btn at-btn-primary" type="button"><?php echo alltools_icon_svg('download',16); ?> Download Premium PDF</button>
                </div>

                <div class="ufx-premium-section-title"><h4>Notes</h4></div>
                <div class="at-field"><label>Payment Terms / Notes</label><textarea id="ufx-inv-notes" class="at-textarea" rows="4" placeholder="Thank you for your business. Payment is due by the due date."></textarea></div>
            </div>
        </div>
    </div>

    <div class="ufx-invoice-preview">
        <div class="ufx-invoice-paper" id="ufx-inv-preview-paper">
            <div class="ufx-preview-topbar" id="ufx-inv-preview-bar"></div>
            <div class="ufx-preview-body">
                <div class="ufx-preview-head">
                    <div class="ufx-preview-logo" id="ufx-inv-preview-logo">Logo</div>
                    <div class="ufx-preview-title">
                        <h2>INVOICE</h2>
                        <p id="ufx-inv-preview-number">INV-001</p>
                        <p id="ufx-inv-preview-dates">Issue — Due</p>
                    </div>
                </div>
                <div class="ufx-preview-parties">
                    <div class="ufx-preview-box"><strong>From</strong><span id="ufx-inv-preview-from">Your Company</span></div>
                    <div class="ufx-preview-box"><strong>Bill To</strong><span id="ufx-inv-preview-to">Client Name</span></div>
                </div>
                <table class="ufx-preview-items">
                    <thead><tr><th>Description</th><th>Qty</th><th>Amount</th></tr></thead>
                    <tbody id="ufx-inv-preview-items"><tr><td>No items yet</td><td>—</td><td>—</td></tr></tbody>
                </table>
                <div class="ufx-preview-totals">
                    <div class="ufx-preview-total-row"><span>Subtotal</span><strong id="ufx-inv-subtotal">$0.00</strong></div>
                    <div class="ufx-preview-total-row"><span>Discount</span><strong id="ufx-inv-discount-total">$0.00</strong></div>
                    <div class="ufx-preview-total-row"><span>Tax</span><strong id="ufx-inv-tax-total">$0.00</strong></div>
                    <div class="ufx-preview-total-row is-final"><span>Total</span><strong id="ufx-inv-total">$0.00</strong></div>
                </div>
            </div>
        </div>
        <div class="ufx-inv-summary-badges">
            <div class="ufx-inv-badge"><span>Items</span><strong id="ufx-inv-count">0</strong></div>
            <div class="ufx-inv-badge"><span>Status</span><strong>Ready</strong></div>
        </div>
        <p class="at-help" style="margin-top:14px;">Premium preview updates live. PDF uses the same branding and correct discount/tax calculations.</p>
    </div>
</div>
<?php }

function alltools_tool_webp_converter() { alltools_render_simple_image_converter('ufx-webp', 'Convert to WebP', 'image/webp', 'webp'); }
function alltools_tool_jpg_to_png() { alltools_render_simple_image_converter('ufx-jpgpng', 'Convert JPG to PNG', 'image/png', 'png', 'image/jpeg,image/jpg'); }
function alltools_tool_png_to_jpg() { alltools_render_simple_image_converter('ufx-pngjpg', 'Convert PNG to JPG', 'image/jpeg', 'jpg', 'image/png', true); }

function alltools_render_simple_image_converter( $prefix, $title, $mime, $ext, $accept = 'image/*', $bg = false ) { ?>
<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h3><?php echo esc_html( $title ); ?></h3>
        <label class="at-dropzone" for="<?php echo esc_attr( $prefix ); ?>-file"><span class="at-dropzone-icon"><?php echo alltools_icon_svg('image',32); ?></span><strong>Choose an image</strong><span>Processed directly in your browser</span></label>
        <input type="file" id="<?php echo esc_attr( $prefix ); ?>-file" accept="<?php echo esc_attr( $accept ); ?>" hidden data-output-mime="<?php echo esc_attr( $mime ); ?>" data-output-ext="<?php echo esc_attr( $ext ); ?>">
        <?php if ( $bg ) : ?><div class="at-field" style="margin-top:16px;"><label>JPG Background</label><input type="color" id="<?php echo esc_attr( $prefix ); ?>-bg" class="at-input" value="#ffffff"></div><?php endif; ?>
        <div class="at-field" style="margin-top:16px;"><label>Quality: <span id="<?php echo esc_attr( $prefix ); ?>-quality-label">90%</span></label><input type="range" id="<?php echo esc_attr( $prefix ); ?>-quality" min="10" max="100" value="90" class="at-range"></div>
        <button id="<?php echo esc_attr( $prefix ); ?>-btn" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('download',16); ?> Convert & Download</button>
    </div>
    <div class="at-card"><h3>Preview</h3><canvas id="<?php echo esc_attr( $prefix ); ?>-canvas" style="max-width:100%;border-radius:16px;background:#f8fafc;"></canvas><div class="at-output-row"><div class="at-output-lbl">Original</div><div class="at-output-val" id="<?php echo esc_attr( $prefix ); ?>-original">—</div></div><div class="at-output-row"><div class="at-output-lbl">Output</div><div class="at-output-val" id="<?php echo esc_attr( $prefix ); ?>-output">—</div></div></div>
</div>
<?php }

function alltools_tool_image_cropper() { ?>
<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h3>Crop Image</h3>
        <label class="at-dropzone" for="ufx-crop-file"><span class="at-dropzone-icon"><?php echo alltools_icon_svg('upload',32); ?></span><strong>Choose an image</strong><span>Set crop box manually</span></label>
        <input type="file" id="ufx-crop-file" accept="image/*" hidden>
        <div class="at-tool-grid at-tool-grid-2" style="margin-top:16px;"><div class="at-field"><label>X</label><input type="number" id="ufx-crop-x" class="at-input" value="0" min="0"></div><div class="at-field"><label>Y</label><input type="number" id="ufx-crop-y" class="at-input" value="0" min="0"></div><div class="at-field"><label>Width</label><input type="number" id="ufx-crop-w" class="at-input" value="500" min="1"></div><div class="at-field"><label>Height</label><input type="number" id="ufx-crop-h" class="at-input" value="500" min="1"></div></div>
        <div class="at-field"><label>Output Format</label><select id="ufx-crop-format" class="at-input"><option value="image/png">PNG</option><option value="image/jpeg">JPG</option><option value="image/webp">WebP</option></select></div>
        <button id="ufx-crop-btn" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('scissors',16); ?> Crop & Download</button>
    </div>
    <div class="at-card"><h3>Preview</h3><canvas id="ufx-crop-canvas" style="max-width:100%;border-radius:16px;background:#f8fafc;"></canvas><p class="at-help" id="ufx-crop-info">Upload an image to preview crop area.</p></div>
</div>
<?php }

/* ================================================================
   ADDITIONAL DEVELOPER TOOLS
   ================================================================ */
function alltools_tool_base64_encoder_decoder() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Input</h3><textarea id="ufx-base64-input" class="at-textarea" rows="14" placeholder="Enter text or Base64..."></textarea><div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;"><button id="ufx-base64-encode" class="at-btn at-btn-primary">Encode</button><button id="ufx-base64-decode" class="at-btn at-btn-outline">Decode</button><button id="ufx-base64-clear" class="at-btn at-btn-outline">Clear</button></div></div><div class="at-card"><h3>Output</h3><textarea id="ufx-base64-output" class="at-textarea" rows="14" readonly></textarea><button id="ufx-base64-copy" class="at-btn at-btn-outline" style="margin-top:14px;"><?php echo alltools_icon_svg('copy',16); ?> Copy</button></div></div>
<?php }

function alltools_tool_jwt_decoder() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>JWT Token</h3><textarea id="ufx-jwt-input" class="at-textarea" rows="10" placeholder="Paste JWT here..."></textarea><button id="ufx-jwt-decode" class="at-btn at-btn-primary" style="margin-top:14px;"><?php echo alltools_icon_svg('shield',16); ?> Decode JWT</button><p class="at-help">This decodes only. It does not verify the signature.</p></div><div class="at-card"><h3>Decoded Output</h3><label>Header</label><textarea id="ufx-jwt-header" class="at-textarea" rows="6" readonly></textarea><label style="margin-top:12px;display:block;">Payload</label><textarea id="ufx-jwt-payload" class="at-textarea" rows="8" readonly></textarea><div class="at-output-row"><div class="at-output-lbl">Signature</div><div class="at-output-val" id="ufx-jwt-signature">—</div></div></div></div>
<?php }

function alltools_tool_url_encoder_decoder() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Input</h3><textarea id="ufx-url-input" class="at-textarea" rows="12" placeholder="Enter URL or text..."></textarea><div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;"><button id="ufx-url-encode" class="at-btn at-btn-primary">Encode</button><button id="ufx-url-decode" class="at-btn at-btn-outline">Decode</button><button id="ufx-url-component" class="at-btn at-btn-outline">Encode Component</button></div></div><div class="at-card"><h3>Output</h3><textarea id="ufx-url-output" class="at-textarea" rows="12" readonly></textarea><button id="ufx-url-copy" class="at-btn at-btn-outline" style="margin-top:14px;"><?php echo alltools_icon_svg('copy',16); ?> Copy</button></div></div>
<?php }

function alltools_tool_uuid_generator() { ?>
<div class="at-card"><h3>UUID v4 Generator</h3><div class="at-field"><label>How many UUIDs?</label><input type="number" id="ufx-uuid-count" class="at-input" value="5" min="1" max="100"></div><button id="ufx-uuid-generate" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('grid',16); ?> Generate UUIDs</button><textarea id="ufx-uuid-output" class="at-textarea" rows="12" style="margin-top:16px;" readonly></textarea><button id="ufx-uuid-copy" class="at-btn at-btn-outline" style="margin-top:14px;"><?php echo alltools_icon_svg('copy',16); ?> Copy</button></div>
<?php }

function alltools_tool_timestamp_converter() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Timestamp to Date</h3><div class="at-field"><label>Unix Timestamp</label><input id="ufx-ts-input" class="at-input" placeholder="1720000000 or milliseconds"></div><button id="ufx-ts-to-date" class="at-btn at-btn-primary">Convert to Date</button><div class="at-output-row"><div class="at-output-lbl">Local Date</div><div class="at-output-val" id="ufx-ts-local">—</div></div><div class="at-output-row"><div class="at-output-lbl">UTC Date</div><div class="at-output-val" id="ufx-ts-utc">—</div></div></div><div class="at-card"><h3>Date to Timestamp</h3><div class="at-field"><label>Date & Time</label><input type="datetime-local" id="ufx-date-input" class="at-input"></div><button id="ufx-date-to-ts" class="at-btn at-btn-primary">Convert to Timestamp</button><div class="at-output-row"><div class="at-output-lbl">Seconds</div><div class="at-output-val" id="ufx-date-sec">—</div></div><div class="at-output-row"><div class="at-output-lbl">Milliseconds</div><div class="at-output-val" id="ufx-date-ms">—</div></div></div></div>
<?php }

function alltools_tool_hash_generator() { ?>
<div class="at-card"><h3>Hash Generator</h3><textarea id="ufx-hash-input" class="at-textarea" rows="8" placeholder="Enter text to hash..."></textarea><button id="ufx-hash-generate" class="at-btn at-btn-primary" style="margin-top:14px;"><?php echo alltools_icon_svg('lock',16); ?> Generate Hashes</button><div id="ufx-hash-output" style="margin-top:16px;"></div></div>
<?php }

function alltools_tool_regex_tester() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Regex</h3><div class="at-field"><label>Pattern</label><input id="ufx-regex-pattern" class="at-input" placeholder="\\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\\.[A-Z]{2,}\\b"></div><div class="at-field"><label>Flags</label><input id="ufx-regex-flags" class="at-input" value="gi"></div><textarea id="ufx-regex-text" class="at-textarea" rows="12" placeholder="Paste sample text here..."></textarea><button id="ufx-regex-test" class="at-btn at-btn-primary" style="margin-top:14px;">Test Regex</button></div><div class="at-card"><h3>Matches</h3><div class="at-output-row"><div class="at-output-lbl">Count</div><div class="at-output-val" id="ufx-regex-count">0</div></div><div id="ufx-regex-results" class="at-file-list" style="margin-top:12px;"></div></div></div>
<?php }

function alltools_tool_cron_expression_generator() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Cron Schedule</h3><div class="at-field"><label>Preset</label><select id="ufx-cron-preset" class="at-input"><option value="hourly">Every hour</option><option value="daily">Every day</option><option value="weekly">Every week</option><option value="monthly">Every month</option><option value="custom">Custom time</option></select></div><div class="at-tool-grid at-tool-grid-2"><div class="at-field"><label>Minute</label><input type="number" id="ufx-cron-min" class="at-input" value="0" min="0" max="59"></div><div class="at-field"><label>Hour</label><input type="number" id="ufx-cron-hour" class="at-input" value="9" min="0" max="23"></div></div><button id="ufx-cron-generate" class="at-btn at-btn-primary">Generate Cron</button></div><div class="at-card"><h3>Expression</h3><textarea id="ufx-cron-output" class="at-textarea" rows="5" readonly></textarea><div class="at-output-row"><div class="at-output-lbl">Meaning</div><div class="at-output-val" id="ufx-cron-meaning">—</div></div><button id="ufx-cron-copy" class="at-btn at-btn-outline"><?php echo alltools_icon_svg('copy',16); ?> Copy</button></div></div>
<?php }

function alltools_tool_password_generator() { ?>
<div class="at-card"><h3>Password Generator</h3><div class="at-tool-grid at-tool-grid-3"><div class="at-field"><label>Length</label><input type="number" id="ufx-pass-length" class="at-input" value="16" min="6" max="64"></div><div class="at-field"><label>Count</label><input type="number" id="ufx-pass-count" class="at-input" value="5" min="1" max="50"></div><div class="at-field"><label>Options</label><label><input type="checkbox" id="ufx-pass-upper" checked> Uppercase</label><br><label><input type="checkbox" id="ufx-pass-lower" checked> Lowercase</label><br><label><input type="checkbox" id="ufx-pass-num" checked> Numbers</label><br><label><input type="checkbox" id="ufx-pass-symbol" checked> Symbols</label></div></div><button id="ufx-pass-generate" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('lock',16); ?> Generate Passwords</button><textarea id="ufx-pass-output" class="at-textarea" rows="10" style="margin-top:16px;" readonly></textarea><button id="ufx-pass-copy" class="at-btn at-btn-outline" style="margin-top:14px;"><?php echo alltools_icon_svg('copy',16); ?> Copy</button></div>
<?php }

function alltools_tool_lorem_ipsum_generator() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Lorem Ipsum Settings</h3><div class="at-field"><label>Type</label><select id="ufx-lorem-type" class="at-input"><option value="paragraphs">Paragraphs</option><option value="sentences">Sentences</option><option value="words">Words</option></select></div><div class="at-field"><label>Amount</label><input type="number" id="ufx-lorem-count" class="at-input" value="3" min="1" max="100"></div><button id="ufx-lorem-generate" class="at-btn at-btn-primary">Generate Text</button></div><div class="at-card"><h3>Output</h3><textarea id="ufx-lorem-output" class="at-textarea" rows="14" readonly></textarea><button id="ufx-lorem-copy" class="at-btn at-btn-outline" style="margin-top:14px;"><?php echo alltools_icon_svg('copy',16); ?> Copy</button></div></div>
<?php }


/* ================================================================
   MORE IMAGE / SOCIAL TOOLS
   ================================================================ */
function alltools_render_preset_resizer_tool( $prefix, $title, $preset_label, $w, $h ) { ?>
<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h3><?php echo esc_html( $title ); ?></h3>
        <label class="at-dropzone" for="<?php echo esc_attr( $prefix ); ?>-file"><span class="at-dropzone-icon"><?php echo alltools_icon_svg('image',32); ?></span><strong>Choose an image</strong><span>Recommended size: <?php echo esc_html( $preset_label ); ?></span></label>
        <input type="file" id="<?php echo esc_attr( $prefix ); ?>-file" accept="image/*" hidden>
        <div class="at-tool-grid at-tool-grid-2" style="margin-top:16px;">
            <div class="at-field"><label>Fit Mode</label><select id="<?php echo esc_attr( $prefix ); ?>-mode" class="at-input"><option value="cover">Cover (crop to fill)</option><option value="contain">Contain (keep whole image)</option></select></div>
            <div class="at-field"><label>Background</label><input type="color" id="<?php echo esc_attr( $prefix ); ?>-bg" class="at-input" value="#ffffff"></div>
        </div>
        <div class="at-output-row" style="margin-top:12px;"><div class="at-output-lbl">Output Size</div><div class="at-output-val"><?php echo esc_html( $w . ' × ' . $h ); ?></div></div>
        <button id="<?php echo esc_attr( $prefix ); ?>-btn" class="at-btn at-btn-primary" style="margin-top:14px;"><?php echo alltools_icon_svg('download',16); ?> Download Image</button>
    </div>
    <div class="at-card"><h3>Preview</h3><canvas id="<?php echo esc_attr( $prefix ); ?>-canvas" width="<?php echo esc_attr( $w ); ?>" height="<?php echo esc_attr( $h ); ?>" style="max-width:100%;height:auto;border-radius:16px;background:#f8fafc;"></canvas><p class="at-help" id="<?php echo esc_attr( $prefix ); ?>-info">Upload an image to preview.</p></div>
</div>
<?php }

function alltools_tool_youtube_thumbnail_size_fixer() { alltools_render_preset_resizer_tool('ufx-yt-thumb','YouTube Thumbnail Size Fixer','1280 × 720',1280,720); }
function alltools_tool_instagram_post_resizer() { alltools_render_preset_resizer_tool('ufx-ig-post','Instagram Post Resizer','1080 × 1080',1080,1080); }
function alltools_tool_facebook_cover_resizer() { alltools_render_preset_resizer_tool('ufx-fb-cover','Facebook Cover Resizer','820 × 312',820,312); }
function alltools_tool_linkedin_banner_resizer() { alltools_render_preset_resizer_tool('ufx-li-banner','LinkedIn Banner Resizer','1584 × 396',1584,396); }
function alltools_tool_twitter_header_resizer() { alltools_render_preset_resizer_tool('ufx-x-header','Twitter / X Header Resizer','1500 × 500',1500,500); }

function alltools_tool_social_media_image_resizer() { ?>
<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h3>Social Media Image Resizer</h3>
        <label class="at-dropzone" for="ufx-social-file"><span class="at-dropzone-icon"><?php echo alltools_icon_svg('image',32); ?></span><strong>Choose an image</strong><span>Resize for popular social platforms</span></label>
        <input type="file" id="ufx-social-file" accept="image/*" hidden>
        <div class="at-tool-grid at-tool-grid-2" style="margin-top:16px;">
            <div class="at-field"><label>Preset</label><select id="ufx-social-preset" class="at-input"><option value="1280x720">YouTube Thumbnail (1280×720)</option><option value="1080x1080">Instagram Square (1080×1080)</option><option value="1080x1350">Instagram Portrait (1080×1350)</option><option value="1080x1920">Instagram Story (1080×1920)</option><option value="820x312">Facebook Cover (820×312)</option><option value="1584x396">LinkedIn Banner (1584×396)</option><option value="1500x500">Twitter / X Header (1500×500)</option></select></div>
            <div class="at-field"><label>Fit Mode</label><select id="ufx-social-mode" class="at-input"><option value="cover">Cover</option><option value="contain">Contain</option></select></div>
        </div>
        <div class="at-field" style="margin-top:16px;"><label>Background</label><input type="color" id="ufx-social-bg" class="at-input" value="#ffffff"></div>
        <button id="ufx-social-btn" class="at-btn at-btn-primary" style="margin-top:14px;"><?php echo alltools_icon_svg('download',16); ?> Resize & Download</button>
    </div>
    <div class="at-card"><h3>Preview</h3><canvas id="ufx-social-canvas" width="1280" height="720" style="max-width:100%;height:auto;border-radius:16px;background:#f8fafc;"></canvas><p class="at-help" id="ufx-social-info">Choose a preset and upload an image.</p></div>
</div>
<?php }

function alltools_tool_image_watermark_tool() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Image Watermark Tool</h3><label class="at-dropzone" for="ufx-watermark-file"><span class="at-dropzone-icon"><?php echo alltools_icon_svg('image',32); ?></span><strong>Choose an image</strong><span>Add a text watermark</span></label><input type="file" id="ufx-watermark-file" accept="image/*" hidden><div class="at-field" style="margin-top:16px;"><label>Watermark Text</label><input id="ufx-watermark-text" class="at-input" value="UptimeFixer"></div><div class="at-tool-grid at-tool-grid-2" style="margin-top:16px;"><div class="at-field"><label>Color</label><input type="color" id="ufx-watermark-color" class="at-input" value="#ffffff"></div><div class="at-field"><label>Opacity %</label><input type="number" id="ufx-watermark-opacity" class="at-input" value="45" min="1" max="100"></div><div class="at-field"><label>Font Size</label><input type="number" id="ufx-watermark-size" class="at-input" value="36" min="10" max="200"></div><div class="at-field"><label>Position</label><select id="ufx-watermark-position" class="at-input"><option value="bottom-right">Bottom Right</option><option value="bottom-left">Bottom Left</option><option value="top-right">Top Right</option><option value="top-left">Top Left</option><option value="center">Center</option></select></div></div><button id="ufx-watermark-btn" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('download',16); ?> Download Watermarked Image</button></div><div class="at-card"><h3>Preview</h3><canvas id="ufx-watermark-canvas" style="max-width:100%;border-radius:16px;background:#f8fafc;"></canvas></div></div>
<?php }

function alltools_tool_image_to_pdf() { ?>
<div class="at-card"><h3>Image to PDF</h3><label class="at-dropzone" for="ufx-imgpdf-files"><span class="at-dropzone-icon"><?php echo alltools_icon_svg('upload',32); ?></span><strong>Choose one or more images</strong><span>Convert JPG, PNG or WebP images into a PDF</span></label><input type="file" id="ufx-imgpdf-files" accept="image/*" multiple hidden><div class="at-tool-grid at-tool-grid-3" style="margin-top:16px;"><div class="at-field"><label>Page Size</label><select id="ufx-imgpdf-size" class="at-input"><option value="a4">A4</option><option value="letter">Letter</option></select></div><div class="at-field"><label>Orientation</label><select id="ufx-imgpdf-ori" class="at-input"><option value="portrait">Portrait</option><option value="landscape">Landscape</option></select></div><div class="at-field"><label>Margin (mm)</label><input type="number" id="ufx-imgpdf-margin" class="at-input" value="10" min="0" max="50"></div></div><button id="ufx-imgpdf-btn" class="at-btn at-btn-primary" style="margin-top:14px;"><?php echo alltools_icon_svg('download',16); ?> Convert to PDF</button><div id="ufx-imgpdf-list" class="at-file-list" style="margin-top:16px;"></div></div>
<?php }

function alltools_tool_bulk_image_compressor() { ?>
<div class="at-card"><h3>Bulk Image Compressor</h3><label class="at-dropzone" for="ufx-bulkcomp-files"><span class="at-dropzone-icon"><?php echo alltools_icon_svg('image',32); ?></span><strong>Choose multiple images</strong><span>Compress directly in your browser</span></label><input type="file" id="ufx-bulkcomp-files" accept="image/*" multiple hidden><div class="at-field" style="margin-top:16px;"><label>Quality: <span id="ufx-bulkcomp-quality-label">80%</span></label><input type="range" id="ufx-bulkcomp-quality" min="20" max="100" value="80" class="at-range"></div><button id="ufx-bulkcomp-btn" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('download',16); ?> Compress Images</button><div id="ufx-bulkcomp-results" class="at-file-list" style="margin-top:16px;"></div></div>
<?php }

/* ================================================================
   MORE WEBSITE / SEO TOOLS
   ================================================================ */
function alltools_tool_meta_tag_generator() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Meta Tag Generator</h3><div class="at-field"><label>Page Title</label><input id="ufx-meta-title" class="at-input" placeholder="Your page title"></div><div class="at-field"><label>Description</label><textarea id="ufx-meta-desc" class="at-textarea" rows="4" placeholder="Write your meta description..."></textarea></div><div class="at-tool-grid at-tool-grid-2"><div class="at-field"><label>Canonical URL</label><input id="ufx-meta-url" class="at-input" placeholder="https://example.com/page"></div><div class="at-field"><label>Image URL</label><input id="ufx-meta-image" class="at-input" placeholder="https://example.com/image.jpg"></div><div class="at-field"><label>Site Name</label><input id="ufx-meta-site" class="at-input" placeholder="Site name"></div><div class="at-field"><label>Twitter Card</label><select id="ufx-meta-card" class="at-input"><option value="summary_large_image">summary_large_image</option><option value="summary">summary</option></select></div></div><button id="ufx-meta-generate" class="at-btn at-btn-primary">Generate Meta Tags</button></div><div class="at-card"><h3>Output</h3><textarea id="ufx-meta-output" class="at-textarea" rows="18" readonly></textarea><button id="ufx-meta-copy" class="at-btn at-btn-outline" style="margin-top:14px;"><?php echo alltools_icon_svg('copy',16); ?> Copy</button></div></div>
<?php }

function alltools_tool_open_graph_preview_tool() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Open Graph Preview</h3><div class="at-field"><label>Site Name</label><input id="ufx-og-site" class="at-input" value="Example Site"></div><div class="at-field"><label>Title</label><input id="ufx-og-title" class="at-input" value="Your Open Graph Title"></div><div class="at-field"><label>Description</label><textarea id="ufx-og-desc" class="at-textarea" rows="4">Your description will appear here.</textarea></div><div class="at-field"><label>Image URL (optional)</label><input id="ufx-og-image" class="at-input" placeholder="https://..."></div><button id="ufx-og-update" class="at-btn at-btn-primary">Update Preview</button></div><div class="at-card"><h3>Card Preview</h3><div id="ufx-og-preview" style="border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;background:#fff;"><div id="ufx-og-imagebox" style="height:180px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;color:#64748b;">Image Preview</div><div style="padding:14px;"><div id="ufx-og-sitename" style="font-size:12px;color:#64748b;text-transform:uppercase;">Example Site</div><div id="ufx-og-title-out" style="font-weight:700;margin-top:6px;">Your Open Graph Title</div><div id="ufx-og-desc-out" style="color:#475569;margin-top:6px;">Your description will appear here.</div></div></div></div></div>
<?php }

function alltools_tool_twitter_card_preview_tool() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Twitter / X Card Preview</h3><div class="at-field"><label>Title</label><input id="ufx-tw-title" class="at-input" value="Your Twitter Card Title"></div><div class="at-field"><label>Description</label><textarea id="ufx-tw-desc" class="at-textarea" rows="4">Your description will appear here.</textarea></div><div class="at-field"><label>Image URL (optional)</label><input id="ufx-tw-image" class="at-input" placeholder="https://..."></div><button id="ufx-tw-update" class="at-btn at-btn-primary">Update Preview</button></div><div class="at-card"><h3>Card Preview</h3><div id="ufx-tw-preview" style="border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;background:#fff;max-width:520px;"><div id="ufx-tw-imagebox" style="height:180px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;color:#64748b;">Image Preview</div><div style="padding:14px;"><div id="ufx-tw-title-out" style="font-weight:700;">Your Twitter Card Title</div><div id="ufx-tw-desc-out" style="color:#475569;margin-top:6px;">Your description will appear here.</div></div></div></div></div>
<?php }

function alltools_tool_keyword_density_checker() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Keyword Density Checker</h3><textarea id="ufx-kd-text" class="at-textarea" rows="16" placeholder="Paste your content here..."></textarea><div class="at-field" style="margin-top:14px;"><label>Focus Keyword (optional)</label><input id="ufx-kd-keyword" class="at-input" placeholder="e.g. website uptime"></div><button id="ufx-kd-analyze" class="at-btn at-btn-primary" style="margin-top:14px;">Analyze</button></div><div class="at-card"><h3>Results</h3><div class="at-output-row"><div class="at-output-lbl">Word Count</div><div class="at-output-val" id="ufx-kd-count">0</div></div><div class="at-output-row"><div class="at-output-lbl">Focus Keyword Density</div><div class="at-output-val" id="ufx-kd-density">0%</div></div><div id="ufx-kd-results" style="margin-top:12px;"></div></div></div>
<?php }

function alltools_tool_serp_preview_tool() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>SERP Preview Tool</h3><div class="at-field"><label>SEO Title</label><input id="ufx-serp-title" class="at-input" maxlength="70" value="Your Page Title"></div><div class="at-field"><label>URL</label><input id="ufx-serp-url" class="at-input" value="https://example.com/page"></div><div class="at-field"><label>Meta Description</label><textarea id="ufx-serp-desc" class="at-textarea" rows="4" maxlength="180">Your meta description appears here.</textarea></div><button id="ufx-serp-update" class="at-btn at-btn-primary">Update Preview</button></div><div class="at-card"><h3>Google Preview</h3><div style="border:1px solid #e5e7eb;border-radius:16px;padding:16px;background:#fff;"><div id="ufx-serp-title-out" style="font-size:22px;line-height:1.3;color:#1a0dab;">Your Page Title</div><div id="ufx-serp-url-out" style="font-size:14px;color:#006621;margin-top:6px;">https://example.com/page</div><div id="ufx-serp-desc-out" style="font-size:14px;color:#4d5156;margin-top:6px;">Your meta description appears here.</div></div><div class="at-output-row" style="margin-top:12px;"><div class="at-output-lbl">Title Length</div><div class="at-output-val" id="ufx-serp-title-len">0</div></div><div class="at-output-row"><div class="at-output-lbl">Description Length</div><div class="at-output-val" id="ufx-serp-desc-len">0</div></div></div></div>
<?php }

function alltools_tool_robots_txt_checker() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Robots.txt Checker</h3><textarea id="ufx-robots-check-input" class="at-textarea" rows="16" placeholder="Paste your robots.txt content here..."></textarea><button id="ufx-robots-check-btn" class="at-btn at-btn-primary" style="margin-top:14px;">Check Robots.txt</button></div><div class="at-card"><h3>Validation Result</h3><div id="ufx-robots-check-results"></div></div></div>
<?php }

function alltools_tool_sitemap_url_extractor() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Sitemap URL Extractor</h3><textarea id="ufx-sitemap-extract-input" class="at-textarea" rows="16" placeholder="Paste XML sitemap content here..."></textarea><button id="ufx-sitemap-extract-btn" class="at-btn at-btn-primary" style="margin-top:14px;">Extract URLs</button></div><div class="at-card"><h3>Extracted URLs</h3><textarea id="ufx-sitemap-extract-output" class="at-textarea" rows="16" readonly></textarea><button id="ufx-sitemap-extract-copy" class="at-btn at-btn-outline" style="margin-top:14px;"><?php echo alltools_icon_svg('copy',16); ?> Copy</button></div></div>
<?php }

function alltools_tool_hreflang_tag_generator() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Hreflang Tag Generator</h3><p class="at-help">Enter one language/region and URL per line using this format: en-US | https://example.com/us</p><textarea id="ufx-hreflang-input" class="at-textarea" rows="12" placeholder="en-US | https://example.com/us&#10;en-GB | https://example.com/uk&#10;x-default | https://example.com/"></textarea><button id="ufx-hreflang-generate" class="at-btn at-btn-primary" style="margin-top:14px;">Generate Hreflang Tags</button></div><div class="at-card"><h3>Output</h3><textarea id="ufx-hreflang-output" class="at-textarea" rows="12" readonly></textarea><button id="ufx-hreflang-copy" class="at-btn at-btn-outline" style="margin-top:14px;"><?php echo alltools_icon_svg('copy',16); ?> Copy</button></div></div>
<?php }

function alltools_tool_schema_markup_generator() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Schema Markup Generator</h3><div class="at-field"><label>Schema Type</label><select id="ufx-schema-type" class="at-input"><option value="Organization">Organization</option><option value="Website">Website</option><option value="Article">Article</option><option value="LocalBusiness">LocalBusiness</option></select></div><div class="at-field"><label>Name / Headline</label><input id="ufx-schema-name" class="at-input" placeholder="Example name"></div><div class="at-field"><label>URL</label><input id="ufx-schema-url" class="at-input" placeholder="https://example.com"></div><div class="at-field"><label>Description</label><textarea id="ufx-schema-desc" class="at-textarea" rows="4"></textarea></div><div class="at-field"><label>Image URL (optional)</label><input id="ufx-schema-image" class="at-input" placeholder="https://example.com/image.jpg"></div><button id="ufx-schema-generate" class="at-btn at-btn-primary">Generate Schema</button></div><div class="at-card"><h3>JSON-LD Output</h3><textarea id="ufx-schema-output" class="at-textarea" rows="16" readonly></textarea><button id="ufx-schema-copy" class="at-btn at-btn-outline" style="margin-top:14px;"><?php echo alltools_icon_svg('copy',16); ?> Copy</button></div></div>
<?php }

function alltools_tool_canonical_url_checker() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Canonical URL Checker</h3><textarea id="ufx-canonical-html" class="at-textarea" rows="16" placeholder="Paste HTML source or a link tag snippet here..."></textarea><button id="ufx-canonical-check" class="at-btn at-btn-primary" style="margin-top:14px;">Find Canonical URL</button></div><div class="at-card"><h3>Result</h3><div class="at-output-row"><div class="at-output-lbl">Canonical URL</div><div class="at-output-val" id="ufx-canonical-result">—</div></div></div></div>
<?php }

/* ================================================================
   MORE DEVELOPER TOOLS
   ================================================================ */
function alltools_tool_html_minifier() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>HTML Minifier</h3><textarea id="ufx-htmlmin-input" class="at-textarea" rows="14" placeholder="Paste HTML here..."></textarea><button id="ufx-htmlmin-run" class="at-btn at-btn-primary" style="margin-top:14px;">Minify HTML</button></div><div class="at-card"><h3>Output</h3><textarea id="ufx-htmlmin-output" class="at-textarea" rows="14" readonly></textarea><button id="ufx-htmlmin-copy" class="at-btn at-btn-outline" style="margin-top:14px;"><?php echo alltools_icon_svg('copy',16); ?> Copy</button></div></div>
<?php }
function alltools_tool_css_minifier() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>CSS Minifier</h3><textarea id="ufx-cssmin-input" class="at-textarea" rows="14" placeholder="Paste CSS here..."></textarea><button id="ufx-cssmin-run" class="at-btn at-btn-primary" style="margin-top:14px;">Minify CSS</button></div><div class="at-card"><h3>Output</h3><textarea id="ufx-cssmin-output" class="at-textarea" rows="14" readonly></textarea><button id="ufx-cssmin-copy" class="at-btn at-btn-outline" style="margin-top:14px;"><?php echo alltools_icon_svg('copy',16); ?> Copy</button></div></div>
<?php }
function alltools_tool_js_minifier() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>JS Minifier</h3><textarea id="ufx-jsmin-input" class="at-textarea" rows="14" placeholder="Paste JavaScript here..."></textarea><button id="ufx-jsmin-run" class="at-btn at-btn-primary" style="margin-top:14px;">Minify JS</button></div><div class="at-card"><h3>Output</h3><textarea id="ufx-jsmin-output" class="at-textarea" rows="14" readonly></textarea><button id="ufx-jsmin-copy" class="at-btn at-btn-outline" style="margin-top:14px;"><?php echo alltools_icon_svg('copy',16); ?> Copy</button></div></div>
<?php }
function alltools_tool_html_beautifier() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>HTML Beautifier</h3><textarea id="ufx-htmlbeaut-input" class="at-textarea" rows="14" placeholder="Paste HTML here..."></textarea><button id="ufx-htmlbeaut-run" class="at-btn at-btn-primary" style="margin-top:14px;">Beautify HTML</button></div><div class="at-card"><h3>Output</h3><textarea id="ufx-htmlbeaut-output" class="at-textarea" rows="14" readonly></textarea><button id="ufx-htmlbeaut-copy" class="at-btn at-btn-outline" style="margin-top:14px;"><?php echo alltools_icon_svg('copy',16); ?> Copy</button></div></div>
<?php }
function alltools_tool_css_beautifier() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>CSS Beautifier</h3><textarea id="ufx-cssbeaut-input" class="at-textarea" rows="14" placeholder="Paste CSS here..."></textarea><button id="ufx-cssbeaut-run" class="at-btn at-btn-primary" style="margin-top:14px;">Beautify CSS</button></div><div class="at-card"><h3>Output</h3><textarea id="ufx-cssbeaut-output" class="at-textarea" rows="14" readonly></textarea><button id="ufx-cssbeaut-copy" class="at-btn at-btn-outline" style="margin-top:14px;"><?php echo alltools_icon_svg('copy',16); ?> Copy</button></div></div>
<?php }
function alltools_tool_color_contrast_checker() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Color Contrast Checker</h3><div class="at-tool-grid at-tool-grid-2"><div class="at-field"><label>Text Color</label><input type="color" id="ufx-contrast-fg" class="at-input" value="#111827"></div><div class="at-field"><label>Background Color</label><input type="color" id="ufx-contrast-bg" class="at-input" value="#ffffff"></div></div><button id="ufx-contrast-check" class="at-btn at-btn-primary" style="margin-top:14px;">Check Contrast</button></div><div class="at-card"><h3>Result</h3><div id="ufx-contrast-preview" style="padding:24px;border-radius:16px;border:1px solid #e5e7eb;font-size:24px;font-weight:700;">Sample Text</div><div class="at-output-row" style="margin-top:12px;"><div class="at-output-lbl">Contrast Ratio</div><div class="at-output-val" id="ufx-contrast-ratio">—</div></div><div class="at-output-row"><div class="at-output-lbl">WCAG AA Normal Text</div><div class="at-output-val" id="ufx-contrast-aa">—</div></div><div class="at-output-row"><div class="at-output-lbl">WCAG AAA Normal Text</div><div class="at-output-val" id="ufx-contrast-aaa">—</div></div></div></div>
<?php }

/* ================================================================
   MORE BUSINESS / PDF TOOLS
   ================================================================ */
function alltools_render_doc_generator( $prefix, $label ) { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3><?php echo esc_html( $label ); ?> Details</h3><div class="at-tool-grid at-tool-grid-2"><div class="at-field"><label>Business Name</label><input id="<?php echo esc_attr( $prefix ); ?>-business" class="at-input" placeholder="Your Company"></div><div class="at-field"><label>Client Name</label><input id="<?php echo esc_attr( $prefix ); ?>-client" class="at-input" placeholder="Client Name"></div></div><div class="at-tool-grid at-tool-grid-3"><div class="at-field"><label><?php echo esc_html( $label ); ?> #</label><input id="<?php echo esc_attr( $prefix ); ?>-number" class="at-input" value="<?php echo esc_attr( strtoupper( substr( $label, 0, 3 ) ) ); ?>-001"></div><div class="at-field"><label>Date</label><input type="date" id="<?php echo esc_attr( $prefix ); ?>-date" class="at-input"></div><div class="at-field"><label>Currency</label><input id="<?php echo esc_attr( $prefix ); ?>-currency" class="at-input" value="$"></div></div><div class="at-field"><label>Items</label><textarea id="<?php echo esc_attr( $prefix ); ?>-items" class="at-textarea" rows="8" placeholder="Service name | 1 | 500&#10;Another item | 2 | 50"></textarea><p class="at-help">One item per line: Description | Quantity | Unit Price</p></div><div class="at-field"><label>Notes</label><textarea id="<?php echo esc_attr( $prefix ); ?>-notes" class="at-textarea" rows="4"></textarea></div><button id="<?php echo esc_attr( $prefix ); ?>-download" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('download',16); ?> Download PDF</button></div><div class="at-card"><h3>Summary</h3><div class="at-output-row"><div class="at-output-lbl">Items</div><div class="at-output-val" id="<?php echo esc_attr( $prefix ); ?>-count">0</div></div><div class="at-output-row"><div class="at-output-lbl">Total</div><div class="at-output-val" id="<?php echo esc_attr( $prefix ); ?>-total">$0.00</div></div></div></div>
<?php }
function alltools_tool_quotation_generator() { alltools_render_doc_generator('ufx-quote','Quotation'); }
function alltools_tool_receipt_generator() { alltools_render_doc_generator('ufx-receipt','Receipt'); }
function alltools_tool_estimate_generator() { alltools_render_doc_generator('ufx-estimate','Estimate'); }

/* ================================================================
   MORE CALCULATORS
   ================================================================ */
function alltools_tool_tax_calculator() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Tax Calculator</h3><div class="at-field"><label>Amount</label><input type="number" id="ufx-tax-amount" class="at-input" value="100" min="0" step="0.01"></div><div class="at-field"><label>Tax Rate %</label><input type="number" id="ufx-tax-rate" class="at-input" value="10" min="0" step="0.01"></div><div class="at-field"><label>Mode</label><select id="ufx-tax-mode" class="at-input"><option value="add">Add tax to amount</option><option value="inclusive">Amount already includes tax</option></select></div><button id="ufx-tax-calc" class="at-btn at-btn-primary">Calculate</button></div><div class="at-card"><h3>Result</h3><div class="at-output-row"><div class="at-output-lbl">Subtotal</div><div class="at-output-val" id="ufx-tax-sub">—</div></div><div class="at-output-row"><div class="at-output-lbl">Tax</div><div class="at-output-val" id="ufx-tax-tax">—</div></div><div class="at-output-row"><div class="at-output-lbl">Total</div><div class="at-output-val" id="ufx-tax-total">—</div></div></div></div>
<?php }
function alltools_tool_profit_margin_calculator() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Profit Margin Calculator</h3><div class="at-field"><label>Cost Price</label><input type="number" id="ufx-margin-cost" class="at-input" value="50" min="0" step="0.01"></div><div class="at-field"><label>Selling Price</label><input type="number" id="ufx-margin-sell" class="at-input" value="80" min="0" step="0.01"></div><button id="ufx-margin-calc" class="at-btn at-btn-primary">Calculate</button></div><div class="at-card"><h3>Result</h3><div class="at-output-row"><div class="at-output-lbl">Profit</div><div class="at-output-val" id="ufx-margin-profit">—</div></div><div class="at-output-row"><div class="at-output-lbl">Margin</div><div class="at-output-val" id="ufx-margin-margin">—</div></div><div class="at-output-row"><div class="at-output-lbl">Markup</div><div class="at-output-val" id="ufx-margin-markup">—</div></div></div></div>
<?php }
function alltools_tool_paypal_fee_calculator() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>PayPal Fee Calculator</h3><div class="at-field"><label>Amount Received</label><input type="number" id="ufx-paypal-amount" class="at-input" value="100" min="0" step="0.01"></div><div class="at-field"><label>Fee %</label><input type="number" id="ufx-paypal-rate" class="at-input" value="3.49" min="0" step="0.01"></div><div class="at-field"><label>Fixed Fee</label><input type="number" id="ufx-paypal-fixed" class="at-input" value="0.49" min="0" step="0.01"></div><button id="ufx-paypal-calc" class="at-btn at-btn-primary">Calculate</button></div><div class="at-card"><h3>Result</h3><div class="at-output-row"><div class="at-output-lbl">Fee</div><div class="at-output-val" id="ufx-paypal-fee">—</div></div><div class="at-output-row"><div class="at-output-lbl">Net Amount</div><div class="at-output-val" id="ufx-paypal-net">—</div></div><div class="at-output-row"><div class="at-output-lbl">Request to Receive This Net</div><div class="at-output-val" id="ufx-paypal-gross">—</div></div></div></div>
<?php }
function alltools_tool_stripe_fee_calculator() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Stripe Fee Calculator</h3><div class="at-field"><label>Amount Charged</label><input type="number" id="ufx-stripe-amount" class="at-input" value="100" min="0" step="0.01"></div><div class="at-field"><label>Fee %</label><input type="number" id="ufx-stripe-rate" class="at-input" value="2.9" min="0" step="0.01"></div><div class="at-field"><label>Fixed Fee</label><input type="number" id="ufx-stripe-fixed" class="at-input" value="0.30" min="0" step="0.01"></div><button id="ufx-stripe-calc" class="at-btn at-btn-primary">Calculate</button></div><div class="at-card"><h3>Result</h3><div class="at-output-row"><div class="at-output-lbl">Fee</div><div class="at-output-val" id="ufx-stripe-fee">—</div></div><div class="at-output-row"><div class="at-output-lbl">Net Amount</div><div class="at-output-val" id="ufx-stripe-net">—</div></div></div></div>
<?php }
function alltools_tool_freelance_hourly_rate_calculator() { ?>
<div class="at-tool-grid at-tool-grid-2"><div class="at-card"><h3>Freelance Hourly Rate Calculator</h3><div class="at-field"><label>Target Annual Income</label><input type="number" id="ufx-free-income" class="at-input" value="50000" min="0" step="0.01"></div><div class="at-field"><label>Annual Business Costs</label><input type="number" id="ufx-free-costs" class="at-input" value="5000" min="0" step="0.01"></div><div class="at-field"><label>Billable Hours Per Year</label><input type="number" id="ufx-free-hours" class="at-input" value="1000" min="1" step="1"></div><button id="ufx-free-calc" class="at-btn at-btn-primary">Calculate</button></div><div class="at-card"><h3>Result</h3><div class="at-output-row"><div class="at-output-lbl">Suggested Hourly Rate</div><div class="at-output-val" id="ufx-free-rate">—</div></div></div></div>
<?php }


/* ================================================================
   NO-API HIGH DEMAND TOOLS
   ================================================================ */
function alltools_tool_pdf_rotate_tool() { ?>
<div class="at-card">
    <h3>PDF Rotate Tool</h3>
    <label class="at-dropzone" for="ufx-pdf-rotate-file"><span class="at-dropzone-icon"><?php echo alltools_icon_svg('upload',32); ?></span><strong>Choose a PDF file</strong><span>Rotate PDF pages in your browser</span></label>
    <input type="file" id="ufx-pdf-rotate-file" accept="application/pdf" hidden>
    <div class="at-tool-grid at-tool-grid-3" style="margin-top:16px;">
        <div class="at-field"><label>Rotation</label><select id="ufx-pdf-rotate-angle" class="at-input"><option value="90">90° clockwise</option><option value="180">180°</option><option value="270">270° clockwise</option></select></div>
        <div class="at-field"><label>Pages</label><input id="ufx-pdf-rotate-pages" class="at-input" placeholder="All or 1,3-5"></div>
        <div class="at-field"><label>Output Name</label><input id="ufx-pdf-rotate-name" class="at-input" placeholder="rotated.pdf"></div>
    </div>
    <button id="ufx-pdf-rotate-btn" class="at-btn at-btn-primary" style="margin-top:14px;"><?php echo alltools_icon_svg('download',16); ?> Rotate PDF</button>
    <div id="ufx-pdf-rotate-result" style="margin-top:16px;"></div>
</div>
<?php }

function alltools_tool_pdf_watermark_tool() { ?>
<div class="at-card">
    <h3>PDF Watermark Tool</h3>
    <label class="at-dropzone" for="ufx-pdf-watermark-file"><span class="at-dropzone-icon"><?php echo alltools_icon_svg('upload',32); ?></span><strong>Choose a PDF file</strong><span>Add text watermark without API</span></label>
    <input type="file" id="ufx-pdf-watermark-file" accept="application/pdf" hidden>
    <div class="at-tool-grid at-tool-grid-3" style="margin-top:16px;">
        <div class="at-field"><label>Watermark Text</label><input id="ufx-pdf-watermark-text" class="at-input" value="CONFIDENTIAL"></div>
        <div class="at-field"><label>Font Size</label><input type="number" id="ufx-pdf-watermark-size" class="at-input" value="48" min="10" max="120"></div>
        <div class="at-field"><label>Opacity %</label><input type="number" id="ufx-pdf-watermark-opacity" class="at-input" value="18" min="1" max="100"></div>
    </div>
    <button id="ufx-pdf-watermark-btn" class="at-btn at-btn-primary" style="margin-top:14px;"><?php echo alltools_icon_svg('download',16); ?> Add Watermark</button>
    <div id="ufx-pdf-watermark-result" style="margin-top:16px;"></div>
</div>
<?php }

function alltools_tool_pdf_page_remover() { ?>
<div class="at-card">
    <h3>PDF Page Remover</h3>
    <label class="at-dropzone" for="ufx-pdf-remove-file"><span class="at-dropzone-icon"><?php echo alltools_icon_svg('upload',32); ?></span><strong>Choose a PDF file</strong><span>Remove selected pages safely in browser</span></label>
    <input type="file" id="ufx-pdf-remove-file" accept="application/pdf" hidden>
    <div class="at-tool-grid at-tool-grid-2" style="margin-top:16px;">
        <div class="at-field"><label>Pages to Remove</label><input id="ufx-pdf-remove-pages" class="at-input" placeholder="Example: 2,4-6"></div>
        <div class="at-field"><label>Output Name</label><input id="ufx-pdf-remove-name" class="at-input" placeholder="cleaned.pdf"></div>
    </div>
    <button id="ufx-pdf-remove-btn" class="at-btn at-btn-primary" style="margin-top:14px;"><?php echo alltools_icon_svg('download',16); ?> Remove Pages</button>
    <div id="ufx-pdf-remove-result" style="margin-top:16px;"></div>
</div>
<?php }

function alltools_tool_resize_image_to_100kb() { ?>
<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h3>Resize Image to Target KB</h3>
        <label class="at-dropzone" for="ufx-img-target-file"><span class="at-dropzone-icon"><?php echo alltools_icon_svg('image',32); ?></span><strong>Choose an image</strong><span>Compress to 50KB, 100KB, 200KB or custom size</span></label>
        <input type="file" id="ufx-img-target-file" accept="image/*" hidden>
        <div class="at-tool-grid at-tool-grid-2" style="margin-top:16px;">
            <div class="at-field"><label>Target Size KB</label><input type="number" id="ufx-img-target-kb" class="at-input" value="100" min="10" max="2000"></div>
            <div class="at-field"><label>Max Width px</label><input type="number" id="ufx-img-target-width" class="at-input" value="1600" min="100" max="5000"></div>
        </div>
        <button id="ufx-img-target-btn" class="at-btn at-btn-primary" style="margin-top:14px;"><?php echo alltools_icon_svg('download',16); ?> Compress Image</button>
    </div>
    <div class="at-card"><h3>Result</h3><canvas id="ufx-img-target-canvas" style="max-width:100%;border-radius:16px;background:#f8fafc;"></canvas><div id="ufx-img-target-info" style="margin-top:12px;"></div></div>
</div>
<?php }

function alltools_tool_passport_photo_maker() { ?>
<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h3>Passport Photo Maker</h3>
        <label class="at-dropzone" for="ufx-passport-file"><span class="at-dropzone-icon"><?php echo alltools_icon_svg('image',32); ?></span><strong>Choose a portrait image</strong><span>Create passport size photo</span></label>
        <input type="file" id="ufx-passport-file" accept="image/*" hidden>
        <div class="at-tool-grid at-tool-grid-2" style="margin-top:16px;">
            <div class="at-field"><label>Size</label><select id="ufx-passport-size" class="at-input"><option value="600x600">2x2 inch / Square</option><option value="413x531">35x45 mm</option><option value="450x600">3:4 Portrait</option></select></div>
            <div class="at-field"><label>Background</label><input type="color" id="ufx-passport-bg" class="at-input" value="#ffffff"></div>
            <div class="at-field"><label>Zoom</label><input type="range" id="ufx-passport-zoom" class="at-range" min="50" max="180" value="100"></div>
            <div class="at-field"><label>Vertical Position</label><input type="range" id="ufx-passport-y" class="at-range" min="-100" max="100" value="0"></div>
        </div>
        <button id="ufx-passport-btn" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('download',16); ?> Download Photo</button>
    </div>
    <div class="at-card"><h3>Preview</h3><canvas id="ufx-passport-canvas" width="600" height="600" style="max-width:100%;border-radius:16px;background:#f8fafc;"></canvas><p class="at-help">For official documents, check your country’s exact photo requirements before submission.</p></div>
</div>
<?php }

function alltools_tool_profile_picture_maker() { ?>
<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h3>Profile Picture Maker</h3>
        <label class="at-dropzone" for="ufx-profile-file"><span class="at-dropzone-icon"><?php echo alltools_icon_svg('image',32); ?></span><strong>Choose an image</strong><span>Create a square or round profile picture</span></label>
        <input type="file" id="ufx-profile-file" accept="image/*" hidden>
        <div class="at-tool-grid at-tool-grid-2" style="margin-top:16px;">
            <div class="at-field"><label>Shape</label><select id="ufx-profile-shape" class="at-input"><option value="circle">Circle PNG</option><option value="square">Square JPG</option></select></div>
            <div class="at-field"><label>Size px</label><input type="number" id="ufx-profile-size" class="at-input" value="800" min="128" max="2000"></div>
            <div class="at-field"><label>Zoom</label><input type="range" id="ufx-profile-zoom" class="at-range" min="50" max="200" value="100"></div>
            <div class="at-field"><label>Background</label><input type="color" id="ufx-profile-bg" class="at-input" value="#ffffff"></div>
        </div>
        <button id="ufx-profile-btn" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('download',16); ?> Download Profile Picture</button>
    </div>
    <div class="at-card"><h3>Preview</h3><canvas id="ufx-profile-canvas" width="800" height="800" style="max-width:100%;border-radius:16px;background:#f8fafc;"></canvas></div>
</div>
<?php }

function alltools_tool_circle_crop_image() { ?>
<div class="at-tool-grid at-tool-grid-2">
    <div class="at-card">
        <h3>Circle Crop Image</h3>
        <label class="at-dropzone" for="ufx-circle-file"><span class="at-dropzone-icon"><?php echo alltools_icon_svg('image',32); ?></span><strong>Choose an image</strong><span>Export transparent circular PNG</span></label>
        <input type="file" id="ufx-circle-file" accept="image/*" hidden>
        <div class="at-field" style="margin-top:16px;"><label>Output Size px</label><input type="number" id="ufx-circle-size" class="at-input" value="800" min="128" max="2000"></div>
        <button id="ufx-circle-btn" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('download',16); ?> Download Circle PNG</button>
    </div>
    <div class="at-card"><h3>Preview</h3><canvas id="ufx-circle-canvas" width="800" height="800" style="max-width:100%;border-radius:16px;background:#f8fafc;"></canvas></div>
</div>
<?php }

function alltools_tool_security_headers_checker() { ?>
<div class="at-card">
    <h3>Security Headers Checker</h3>
    <div class="at-input-group"><input id="ufx-security-url" class="at-input" placeholder="https://example.com"><button id="ufx-security-check" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('shield',16); ?> Check Headers</button></div>
    <div id="ufx-security-results" style="margin-top:16px;"></div>
</div>
<?php }

function alltools_tool_mixed_content_checker() { ?>
<div class="at-card">
    <h3>Mixed Content Checker</h3>
    <div class="at-input-group"><input id="ufx-mixed-url" class="at-input" placeholder="https://example.com"><button id="ufx-mixed-check" class="at-btn at-btn-primary"><?php echo alltools_icon_svg('shield',16); ?> Check Mixed Content</button></div>
    <div id="ufx-mixed-results" style="margin-top:16px;"></div>
</div>
<?php }
