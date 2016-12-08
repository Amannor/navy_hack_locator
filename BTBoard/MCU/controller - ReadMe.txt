In order to work on the code provided for the controller - 
(note: You can also follow steps 1&2 and then use SDK BLE examples - they are an excellent starting point. The code used in the hackathon will not connect to BLE devices but was used as a demo)
1. Install and use Keil uVision IDE.
2. Download nRF5 SDK - https://www.nordicsemi.com/eng/Products/Bluetooth-low-energy/nRF5-SDK. Full support is availble there and at - https://devzone.nordicsemi.com
3. Either extract the radio_hack.ZIP file to - nRF5_SDK_11.0.0_89a8197\examples\peripheral or replace the main.c file in nRF5_SDK_11.0.0_89a8197\examples\peripheral\radio\receiver with included main.c
4. Set 'server_flag 0' for end point.
5. Set 'server_flag 1' for node.
6. Build and download to devices - in Hackathon 2 nRF51-Dongles(pca10031) were used. However I would suggest using NRF51 devkit(pca10028?) for node. The 
7. PuTTY can be used to monitor UART communication.
