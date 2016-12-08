/* Copyright (c) 2014 Nordic Semiconductor. All Rights Reserved.
 *
 * The information contained herein is property of Nordic Semiconductor ASA.
 * Terms and conditions of usage are described in detail in NORDIC
 * SEMICONDUCTOR STANDARD SOFTWARE LICENSE AGREEMENT.
 *
 * Licensees are granted free, non-transferable use of the information. NO
 * WARRANTY of ANY KIND is provided. This heading must NOT be removed from
 * the file.
 *
 */

/** @file
*
* @defgroup nrf_dev_radio_rx_example_main main.c
* @{
* @ingroup nrf_dev_radio_rx_example
* @brief Radio Receiver example Application main file.
*
* This file contains the source code for a sample application using the NRF_RADIO peripheral. 
*
*/
#include <stdint.h>
#include <stdbool.h>
#include <stdio.h>
#include "radio_config.h"
#include "nrf_gpio.h"
#include "boards.h"
#include "bsp.h"
#include "app_timer.h"
#include "nordic_common.h"
#include "nrf_error.h"
#include "nrf_delay.h"

#define APP_TIMER_PRESCALER      0                     /**< Value of the RTC1 PRESCALER register. */
#define APP_TIMER_OP_QUEUE_SIZE  2                     /**< Size of timer operation queues. */

#define UART_TX_BUF_SIZE 256                           /**< UART TX buffer size. */
#define UART_RX_BUF_SIZE 1                             /**< UART RX buffer size. */
#define server_flag 0

static uint32_t                   r_packet;              /**< Packet to transmit. */
static uint32_t                   s_packet=20; 

void send_packet()
{
    // send the packet:
    NRF_RADIO->EVENTS_READY = 0U;
    NRF_RADIO->TASKS_TXEN   = 1;

    while (NRF_RADIO->EVENTS_READY == 0U)
    {
        // wait
    }
    NRF_RADIO->EVENTS_END  = 0U;
    NRF_RADIO->TASKS_START = 1U;

    while (NRF_RADIO->EVENTS_END == 0U)
    {
        // wait
    }

    uint32_t err_code = bsp_indication_text_set(BSP_INDICATE_SENT_OK, "");
    APP_ERROR_CHECK(err_code);

    NRF_RADIO->EVENTS_DISABLED = 0U;
    // Disable radio
    NRF_RADIO->TASKS_DISABLE = 1U;

    while (NRF_RADIO->EVENTS_DISABLED == 0U)
    {
        // wait
    }
}
void uart_error_handle(app_uart_evt_t * p_event)
{
   // No implementation needed.
}

/**@brief Function for initialization oscillators.
 */
void clock_initialization()
{
    /* Start 16 MHz crystal oscillator */
    NRF_CLOCK->EVENTS_HFCLKSTARTED = 0;
    NRF_CLOCK->TASKS_HFCLKSTART    = 1;

    /* Wait for the external oscillator to start up */
    while (NRF_CLOCK->EVENTS_HFCLKSTARTED == 0)
    {
        // Do nothing.
    }

    /* Start low frequency crystal oscillator for app_timer(used by bsp)*/
    NRF_CLOCK->LFCLKSRC            = (CLOCK_LFCLKSRC_SRC_Xtal << CLOCK_LFCLKSRC_SRC_Pos);
    NRF_CLOCK->EVENTS_LFCLKSTARTED = 0;
    NRF_CLOCK->TASKS_LFCLKSTART    = 1;

    while (NRF_CLOCK->EVENTS_LFCLKSTARTED == 0)
    {
        // Do nothing.
    }
}


/**@brief Function for reading packet.
 */
uint32_t read_packet()
{
    uint32_t result = 0;

    NRF_RADIO->EVENTS_READY = 0U;
    // Enable radio and wait for ready
    NRF_RADIO->TASKS_RXEN = 1U;

    while (NRF_RADIO->EVENTS_READY == 0U)
    {
        // wait
    }
    NRF_RADIO->EVENTS_END = 0U;
    // Start listening and wait for address received event
    NRF_RADIO->TASKS_START = 1U;
	nrf_delay_ms(10);
		if (server_flag){
			// Wait for end of packet or buttons state changed
				while (NRF_RADIO->EVENTS_END == 0U)
				{
						// wait
				}
		}
    if (NRF_RADIO->CRCSTATUS == 1U)
    {
        result = r_packet;
    }
    NRF_RADIO->EVENTS_DISABLED = 0U;
    // Disable radio
    NRF_RADIO->TASKS_DISABLE = 1U;

    while (NRF_RADIO->EVENTS_DISABLED == 0U)
    {
        // wait
    }
    return result;
}


/**
 * @brief Function for application main entry.
 * @return 0. int return type required by ANSI/ISO standard.
 */
int main(void)
{
    uint32_t err_code = NRF_SUCCESS;
		
    clock_initialization();
    APP_TIMER_INIT(APP_TIMER_PRESCALER, APP_TIMER_OP_QUEUE_SIZE, NULL);
	nrf_gpio_cfg_output(BSP_LED_0);
	nrf_gpio_pin_set(BSP_LED_0);
	nrf_gpio_cfg_output(BSP_LED_1);
	nrf_gpio_pin_set(BSP_LED_1);
	nrf_gpio_cfg_output(BSP_LED_2);
	nrf_gpio_pin_set(BSP_LED_2);
    const app_uart_comm_params_t comm_params =  
    {
        RX_PIN_NUMBER, 
        TX_PIN_NUMBER, 
        RTS_PIN_NUMBER, 
        CTS_PIN_NUMBER, 
        APP_UART_FLOW_CONTROL_ENABLED, 
        false, 
        UART_BAUDRATE_BAUDRATE_Baud115200
    };   
    APP_UART_FIFO_INIT(&comm_params, 
                       UART_RX_BUF_SIZE, 
                       UART_TX_BUF_SIZE, 
                       uart_error_handle, 
                       APP_IRQ_PRIORITY_LOW,
                       err_code);
    APP_ERROR_CHECK(err_code);
    //err_code = bsp_init(BSP_INIT_LED, APP_TIMER_TICKS(100, APP_TIMER_PRESCALER), NULL);
    APP_ERROR_CHECK(err_code);

    // Set radio configuration parameters
    radio_configure();
    //NRF_RADIO->PACKETPTR = (uint32_t)&r_packet;

    err_code = bsp_indication_text_set(BSP_INDICATE_USER_STATE_OFF, "Wait for first packet\n\r");
    APP_ERROR_CHECK(err_code);

    while (true)
    {

      NRF_RADIO->PACKETPTR = (uint32_t)&r_packet;
			uint32_t received = read_packet();
      //err_code = bsp_indication_text_set(BSP_INDICATE_RCV_OK, "");
      //APP_ERROR_CHECK(err_code);
			if((unsigned int)received>0) {
					printf("%u\n\r", (unsigned int)received);
					if(!server_flag){
							switch(received){
									case 1:	nrf_gpio_pin_set(BSP_LED_0);
													nrf_gpio_pin_set(BSP_LED_1);
													nrf_gpio_pin_set(BSP_LED_2);
													break;
									case 2: nrf_gpio_pin_clear(BSP_LED_0);
													nrf_gpio_pin_set(BSP_LED_1);
													nrf_gpio_pin_set(BSP_LED_2);
													break;
									case 3: nrf_gpio_pin_set(BSP_LED_0);
													nrf_gpio_pin_clear(BSP_LED_1);
													nrf_gpio_pin_set(BSP_LED_2);
													break;
									case 4: nrf_gpio_pin_set(BSP_LED_0);
													nrf_gpio_pin_set(BSP_LED_1);
													nrf_gpio_pin_clear(BSP_LED_2);
													break;
							}
					}
			}
			else{
				;}
			
			r_packet=0;			
			nrf_delay_ms(500);
			NRF_RADIO->PACKETPTR = (uint32_t)&s_packet;
			if (server_flag){
				uint8_t t;
				err_code = app_uart_get(&t);
				if (NRF_ERROR_NOT_FOUND == err_code)
						{
             s_packet=9;
						}
        else if (NRF_SUCCESS == err_code){
						s_packet=t;
				}
			}
			else{
				s_packet=20;
			}
			send_packet();
			nrf_delay_ms(500);
		}
}

/**
 *@}
 **/
