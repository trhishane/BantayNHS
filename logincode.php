<?php

// ... existing code ...
                    $_SESSION['auth'] = true;
                    $_SESSION['auth_role'] = $row['role_as']; // Make sure this is setting role_as = 2 for teachers
                    $_SESSION['auth_user'] = [
                        'user_id' => $row['id'],
                        'name' => $row['name'],
                        'email' => $row['email'],
                    ];
// ... existing code ... 