# File Upload Security

> **Last updated:** 2026-07-11
> **Canonical owner:** Development Team
> **Related:** `SECURITY.md` (A01, A03 sections)

---

## Current Upload Points

| Upload Point | File Types | Location | Auth Required |
|-------------|-----------|----------|---------------|
| Blog banner images | JPEG, PNG, WebP, GIF | Uploaded/stored in `uploads/` or `media/` | Admin (blogs.edit) |
| Festival/cause images | JPEG, PNG, WebP, GIF | Stored in `images/` | Admin (festivals.edit) |
| Panihati offline CSV/XLS | CSV, XLS, XLSX | Admin import | Admin (panihati.edit) |

## Current Controls

- File types restricted by extension check (not MIME validation)
- No file size limits in application code
- Filenames are stored as provided (not randomized)
- Upload directory within web root

## Required Security Standards

### For All New Upload Features

1. **MIME Type Validation**
   ```php
   $finfo = finfo_open(FILEINFO_MIME_TYPE);
   $mimeType = finfo_file($finfo, $_FILES['file']['tmp_name']);
   $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
   if (!in_array($mimeType, $allowedMimes)) {
       // Reject file
   }
   ```

2. **Randomized Filenames**
   ```php
   $ext = pathinfo($filename, PATHINFO_EXTENSION);
   $newFilename = bin2hex(random_bytes(16)) . '.' . $ext;
   ```

3. **Maximum File Size**
   ```php
   $maxSize = 5 * 1024 * 1024; // 5MB
   if ($_FILES['file']['size'] > $maxSize) {
       // Reject
   }
   ```

4. **Extension + MIME Double Validation**
   - Check file extension against allowlist
   - Check MIME type via `finfo` (not just `$_FILES['type']` which is client-controlled)

5. **No Executable Files**
   - Reject `.php`, `.exe`, `.sh`, `.pl`, `.py`, `.htaccess`, `.asp`, `.jsp`, `.ini`, `.bat`, `.cmd`

6. **Store Outside Web Root When Possible**
   - Store files outside `public_html/` and serve through a PHP script
   - If files must be in web root, ensure `.htaccess` prevents PHP execution in upload directories

### Current Gaps

| Gap | Risk | Mitigation Needed |
|-----|------|------------------|
| No MIME validation — extension only | Medium | Add `finfo` check |
| No filename randomization | Medium | Add `bin2hex(random_bytes(16))` naming |
| No file size limit in code | Low | Add PHP check |
| Uploads in web root | Medium | Move uploads above web root or add `.htaccess` protection |
| No EXIF stripping | Low | Add EXIF removal for images |
