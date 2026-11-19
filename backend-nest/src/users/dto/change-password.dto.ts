import { IsNotEmpty, IsString, MinLength } from 'class-validator';

export class ChangePasswordDto {
  @IsString()
  @IsNotEmpty({ message: 'Current password is required' })
  current_password: string;

  @IsString()
  @MinLength(7, { message: 'New password must be more than 6 characters' })
  new_password: string;

  @IsString()
  @IsNotEmpty({ message: 'Confirm password is required' })
  confirm_new_password: string;
}
