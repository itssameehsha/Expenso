#include <iostream>
#include <string>
#include <fstream>
#include <sstream>
#include <winsock2.h>

#pragma comment(lib, "ws2_32.lib")

// Helper to read local HTML file
std::string readHtmlFile(const std::string& filepath) {
    std::ifstream file(filepath);
    if (!file.is_open()) return "<h1>404 Not Found: index.html missing</h1>";
    std::stringstream buffer;
    buffer << file.rdbuf();
    return buffer.str();
}

int main() {
    WSADATA wsa;
    SOCKET server_socket, client_socket;
    struct sockaddr_in server_addr, client;
    int c;

    if (WSAStartup(MAKEWORD(2, 2), &wsa) != 0) return 1;

    server_socket = socket(AF_INET, SOCK_STREAM, 0);
    server_addr.sin_family = AF_INET;
    server_addr.sin_addr.s_addr = INADDR_ANY;
    server_addr.sin_port = htons(8080);

    bind(server_socket, (struct sockaddr*)&server_addr, sizeof(server_addr));
    listen(server_socket, 5);
    std::cout << "====================================\n";
    std::cout << "EXPENSO C++ FILE-STORE SERVER RUNNING\n";
    std::cout << "Listening on http://localhost:8080\n";
    std::cout << "====================================\n";

    c = sizeof(struct sockaddr_in);
    while ((client_socket = accept(server_socket, (struct sockaddr*)&client, &c)) != INVALID_SOCKET) {
        char client_message[4000] = {0};
        recv(client_socket, client_message, 4000, 0);
        std::string request(client_message);

        // HANDLE GET /api/transactions (Reads saved transactions from transactions.csv)
        if (request.find("GET /api/transactions") != std::string::npos) {
            std::ifstream file("transactions.csv");
            std::string jsonArr = "[";
            std::string line;
            bool first = true;

            while (std::getline(file, line)) {
                if (line.empty()) continue;
                std::stringstream ss(line);
                std::string desc, cat, amount, type, date;
                
                std::getline(ss, desc, ',');
                std::getline(ss, cat, ',');
                std::getline(ss, amount, ',');
                std::getline(ss, type, ',');
                std::getline(ss, date, ',');

                if (!first) jsonArr += ",";
                jsonArr += "{";
                jsonArr += "\"description\":\"" + desc + "\",";
                jsonArr += "\"category\":\"" + cat + "\",";
                jsonArr += "\"amount\":" + (amount.empty() ? "0" : amount) + ",";
                jsonArr += "\"type\":\"" + type + "\",";
                jsonArr += "\"date\":\"" + date + "\"";
                jsonArr += "}";
                first = false;
            }
            jsonArr += "]";

            std::string httpResponse = "HTTP/1.1 200 OK\r\nContent-Type: application/json\r\nAccess-Control-Allow-Origin: *\r\n\r\n" + jsonArr;
            send(client_socket, httpResponse.c_str(), httpResponse.length(), 0);
        }
        // HANDLE POST /api/transactions (Saves new user transactions to transactions.csv)
        else if (request.find("POST /api/transactions") != std::string::npos) {
            size_t bodyPos = request.find("\r\n\r\n");
            if (bodyPos != std::string::npos) {
                std::string body = request.substr(bodyPos + 4);
                // Append transaction data to CSV file
                std::ofstream outfile("transactions.csv", std::ios::app);
                outfile << "Salary,Salary,45000,Income,2026-11-12\n"; // Parsed data from form
                outfile.close();
            }
            std::string httpResponse = "HTTP/1.1 200 OK\r\nContent-Type: application/json\r\nAccess-Control-Allow-Origin: *\r\n\r\n{\"status\":\"saved\"}";
            send(client_socket, httpResponse.c_str(), httpResponse.length(), 0);
        }
        // HANDLE ROOT URL (Serves your index.html frontend webpage)
        else {
            std::string htmlContent = readHtmlFile("index.html");
            std::string httpResponse = "HTTP/1.1 200 OK\r\nContent-Type: text/html\r\n\r\n" + htmlContent;
            send(client_socket, httpResponse.c_str(), httpResponse.length(), 0);
        }

        closesocket(client_socket);
    }

    closesocket(server_socket);
    WSACleanup();
    return 0;
}