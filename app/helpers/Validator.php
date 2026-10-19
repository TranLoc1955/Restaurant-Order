<?php
/**
 * Validator Helper
 * Kiểm tra tính hợp lệ của dữ liệu đầu vào (Form & API requests)
 */

class Validator {
    private array $data;
    private array $errors = [];

    public function __construct(array $data) {
        $this->data = $data;
    }

    public static function make(array $data): self {
        return new self($data);
    }

    /**
     * Bắt buộc phải có và không được để trống
     */
    public function required(string $field, string $fieldName): self {
        $value = trim($this->data[$field] ?? '');
        if ($value === '') {
            $this->errors[$field] = "Vui lòng nhập {$fieldName}.";
        }
        return $this;
    }

    /**
     * Kiểm tra định dạng Email hợp lệ
     */
    public function email(string $field, string $fieldName = 'Email'): self {
        if (!empty($this->data[$field])) {
            $email = trim($this->data[$field]);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field] = "{$fieldName} không đúng định dạng hợp lệ.";
            }
        }
        return $this;
    }

    /**
     * Kiểm tra định dạng số điện thoại Việt Nam (10 số, bắt đầu bằng 0)
     */
    public function phone(string $field, string $fieldName = 'Số điện thoại'): self {
        if (!empty($this->data[$field])) {
            $phone = trim($this->data[$field]);
            if (!preg_match('/^(0[35789])[0-9]{8}$/', $phone)) {
                $this->errors[$field] = "{$fieldName} phải là 10 chữ số hợp lệ của Việt Nam (bắt đầu bằng 03, 05, 07, 08, 09).";
            }
        }
        return $this;
    }

    /**
     * Độ dài tối thiểu
     */
    public function minLength(string $field, int $min, string $fieldName): self {
        if (!empty($this->data[$field])) {
            if (mb_strlen(trim($this->data[$field])) < $min) {
                $this->errors[$field] = "{$fieldName} phải có ít nhất {$min} ký tự.";
            }
        }
        return $this;
    }

    /**
     * Độ dài tối đa
     */
    public function maxLength(string $field, int $max, string $fieldName): self {
        if (!empty($this->data[$field])) {
            if (mb_strlen(trim($this->data[$field])) > $max) {
                $this->errors[$field] = "{$fieldName} không được vượt quá {$max} ký tự.";
            }
        }
        return $this;
    }

    /**
     * So khớp hai trường (Ví dụ: Mật khẩu và Xác nhận mật khẩu)
     */
    public function matches(string $field, string $matchField, string $message): self {
        $val1 = $this->data[$field] ?? '';
        $val2 = $this->data[$matchField] ?? '';
        if ($val1 !== $val2) {
            $this->errors[$matchField] = $message;
        }
        return $this;
    }

    /**
     * Kiểm tra giá trị số dương
     */
    public function positiveNumber(string $field, string $fieldName): self {
        if (isset($this->data[$field])) {
            $val = $this->data[$field];
            if (!is_numeric($val) || (float)$val <= 0) {
                $this->errors[$field] = "{$fieldName} phải là một số lớn hơn 0.";
            }
        }
        return $this;
    }

    /**
     * Kiểm tra giá trị số nguyên không âm (>= 0)
     */
    public function nonNegativeInteger(string $field, string $fieldName): self {
        if (isset($this->data[$field])) {
            $val = $this->data[$field];
            if (!is_numeric($val) || (int)$val < 0) {
                $this->errors[$field] = "{$fieldName} phải là số nguyên không âm.";
            }
        }
        return $this;
    }

    /**
     * Kiểm tra giá trị nằm trong danh sách cho phép (in array)
     */
    public function in(string $field, array $allowed, string $fieldName): self {
        if (isset($this->data[$field])) {
            if (!in_array($this->data[$field], $allowed, true)) {
                $this->errors[$field] = "{$fieldName} không thuộc giá trị được phép.";
            }
        }
        return $this;
    }

    /**
     * Thêm lỗi tùy chỉnh từ bên ngoài (ví dụ email đã tồn tại trong DB)
     */
    public function addError(string $field, string $message): self {
        $this->errors[$field] = $message;
        return $this;
    }

    /**
     * Trả về danh sách lỗi
     */
    public function errors(): array {
        return $this->errors;
    }

    /**
     * Lấy lỗi đầu tiên
     */
    public function firstError(): ?string {
        if (empty($this->errors)) {
            return null;
        }
        return reset($this->errors);
    }

    /**
     * Kiểm tra xem việc xác thực có thất bại không
     */
    public function fails(): bool {
        return !empty($this->errors);
    }

    /**
     * Kiểm tra việc xác thực có thành công không
     */
    public function passes(): bool {
        return empty($this->errors);
    }

    /**
     * Lọc và làm sạch chuỗi chống XSS
     */
    public static function sanitize(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
