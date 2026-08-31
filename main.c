/* USER CODE BEGIN Includes */
#include <stdio.h>
/* USER CODE END Includes */

/* USER CODE BEGIN PV */
volatile uint8_t countA = 50;
volatile uint8_t countB = 50;

volatile uint8_t mode = 1;             // 1 = UP, 0 = DN
volatile uint8_t lcd_update = 0;

volatile uint32_t timeGateA = 0;
volatile uint32_t timeGateB = 0;
volatile uint32_t timeMode = 0;
volatile uint32_t timeReset = 0;
/* USER CODE END PV */

/* USER CODE BEGIN 0 */
void lcd_write_nibble(uint8_t rs, uint8_t data)
{
    HAL_GPIO_WritePin(RS_GPIO_Port, RS_Pin,
                      rs ? GPIO_PIN_SET : GPIO_PIN_RESET);

    HAL_GPIO_WritePin(D4_GPIO_Port, D4_Pin,
                      (data & 0x01) ? GPIO_PIN_SET : GPIO_PIN_RESET);

    HAL_GPIO_WritePin(D5_GPIO_Port, D5_Pin,
                      (data & 0x02) ? GPIO_PIN_SET : GPIO_PIN_RESET);

    HAL_GPIO_WritePin(D6_GPIO_Port, D6_Pin,
                      (data & 0x04) ? GPIO_PIN_SET : GPIO_PIN_RESET);

    HAL_GPIO_WritePin(D7_GPIO_Port, D7_Pin,
                      (data & 0x08) ? GPIO_PIN_SET : GPIO_PIN_RESET);

    HAL_GPIO_WritePin(E_GPIO_Port, E_Pin, GPIO_PIN_SET);

    for(uint8_t i = 0; i < 72; i++)
    {
        __NOP();
    }

    HAL_GPIO_WritePin(E_GPIO_Port, E_Pin, GPIO_PIN_RESET);

    for(uint8_t i = 0; i < 72; i++)
    {
        __NOP();
    }
}


void lcd_send_cmd(uint8_t cmd)
{
    lcd_write_nibble(0, (cmd >> 4) & 0x0F);
    lcd_write_nibble(0, cmd & 0x0F);

    HAL_Delay(2);
}


void lcd_send_data(uint8_t data)
{
    lcd_write_nibble(1, (data >> 4) & 0x0F);
    lcd_write_nibble(1, data & 0x0F);

    HAL_Delay(2);
}


void lcd_init(void)
{
    HAL_Delay(20);

    lcd_write_nibble(0, 0x03);
    HAL_Delay(5);

    lcd_write_nibble(0, 0x03);
    HAL_Delay(1);

    lcd_write_nibble(0, 0x03);
    HAL_Delay(1);

    lcd_write_nibble(0, 0x02);
    HAL_Delay(1);

    lcd_send_cmd(0x28);     // LCD 4 bit, 2 dong, 5x8
    lcd_send_cmd(0x0C);     // Bat LCD, tat con tro
    lcd_send_cmd(0x01);     // Xoa man hinh
    lcd_send_cmd(0x06);     // Tang dia chi, khong dich man hinh
}


void lcd_display(char *data)
{
    while(*data)
    {
        lcd_send_data((uint8_t)*data);
        data++;
    }
}


void lcd_gotoxy(uint8_t row, uint8_t col)
{
    uint8_t coordinates = 0;

    switch(row)
    {
        case 0:
            coordinates = 0x80 | col;
            break;

        case 1:
            coordinates = 0xC0 | col;
            break;
    }

    lcd_send_cmd(coordinates);
}


void lcd_update_display(void)
{
    char line2[17];

    // Dong 1: 16 ky tu
    lcd_gotoxy(0, 0);
    lcd_display("[A]  Counter [B]");

    // Dong 2: 16 ky tu
    snprintf(line2, sizeof(line2),
             "%3u   [%s]   %3u",
             countA,
             mode ? "UP" : "DN",
             countB);

    lcd_gotoxy(1, 0);
    lcd_display(line2);
}

/* USER CODE END 0 */

/* USER CODE BEGIN 2 */

lcd_init();
lcd_update_display();

/* USER CODE END 2 */

/* USER CODE BEGIN 3 */

if(lcd_update)
{
    lcd_update = 0;
    lcd_update_display();
}

/* USER CODE END 3 */

/* USER CODE BEGIN 4 */

void HAL_GPIO_EXTI_Callback(uint16_t GPIO_Pin)
{
    uint32_t time = HAL_GetTick();

    /* NUT SENSOR A */
    if(GPIO_Pin == GATEA_Pin)
    {
        if((time - timeGateA) >= 50)
        {
            timeGateA = time;

            if(mode == 1)
            {
                if(countA < 100)
                {
                    countA++;
                }
            }
            else
            {
                if(countA > 0)
                {
                    countA--;
                }
            }

            lcd_update = 1;
        }
    }

    /* NUT SENSOR B */
    else if(GPIO_Pin == GATEB_Pin)
    {
        if((time - timeGateB) >= 50)
        {
            timeGateB = time;

            if(mode == 1)
            {
                if(countB < 100)
                {
                    countB++;
                }
            }
            else
            {
                if(countB > 0)
                {
                    countB--;
                }
            }

            lcd_update = 1;
        }
    }

    /* NUT MODE */
    else if(GPIO_Pin == MODE_Pin)
    {
        if((time - timeMode) >= 50)
        {
            timeMode = time;

            mode = !mode;

            lcd_update = 1;
        }
    }

    /* NUT RESET / SET */
    else if(GPIO_Pin == RESET_Pin)
    {
        if((time - timeReset) >= 50)
        {
            timeReset = time;

            countA = 50;
            countB = 50;

            lcd_update = 1;
        }
    }
}

/* USER CODE END 4 */