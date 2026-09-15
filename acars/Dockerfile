FROM mcr.microsoft.com/dotnet/sdk:8.0 AS build
WORKDIR /src
COPY acars/ ./
RUN dotnet publish Promethee.Acars.csproj -f net8.0 -c Release -o /out --self-contained false

FROM mcr.microsoft.com/dotnet/aspnet:8.0
WORKDIR /app
COPY --from=build /out/ ./
ENV ASPNETCORE_URLS=http://0.0.0.0:1974
EXPOSE 1974
ENTRYPOINT ["dotnet", "Promethee.Acars.dll"]
